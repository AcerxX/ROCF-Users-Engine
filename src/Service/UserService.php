<?php

namespace App\Service;

use App\Dto\UserChangesDto;
use App\Dto\UserEditRequestDto;
use App\Dto\UserRequestDto;
use App\Entity\Token;
use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

class UserService
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param null|string $locale
     */
    public function setLocaleForTranslator(?string $locale): void
    {
        if (empty($locale)) {
            return;
        }

        $this->locale = $locale;
        $this->translator->setLocale($locale);
    }

    /**
     * UserService constructor.
     * @param RegistryInterface $doctrine
     * @param TranslatorInterface $translator
     * @param string $_locale
     */
    public function __construct(RegistryInterface $doctrine, TranslatorInterface $translator, string $_locale)
    {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $this->locale = $_locale;

        $this->translator->setLocale($this->locale);
    }

    /**
     * @param $item
     * @param array $validators
     * @param string $fieldTranslationKey
     * @return string
     */
    private function validateItem($item, array $validators, string $fieldTranslationKey = ''): string
    {
        $errorMessage = '';
        $validator = Validation::createValidator();

        $violations = $validator->validate($item, $validators);
        $fieldTranslated = $this->translator->trans($fieldTranslationKey);
        foreach ($violations as $violation) {
            $errorMessage .= $fieldTranslated . ' ' . $this->translator->trans($violation->getMessage()) . ' ';
        }

        return $errorMessage;
    }

    /**
     * @param UserEditRequestDto $userEditRequestDto
     * @return array
     * @throws UserNotFoundException
     */
    public function editUserProfile(UserEditRequestDto $userEditRequestDto): array
    {
        $this->validateEditProfileRequest($userEditRequestDto);
        $user = $this->getUserFromDatabase($userEditRequestDto->getEmail());
        $user = $this->editUserByRequest($user, $userEditRequestDto->getChanges());

        return $this->formatUserForResponse($user);
    }

    /**
     * @param UserEditRequestDto $userEditRequestDto
     */
    private function validateEditProfileRequest(UserEditRequestDto $userEditRequestDto): void
    {
        $errorMessage = '';

        $emailValidator = new Email();
        $notBlankValidator = new NotBlank();

        $errorMessage .= $this->validateItem(
            $userEditRequestDto->getEmail(),
            [
                $emailValidator,
                $notBlankValidator
            ],
            'validation.email'
        );

        if (null !== $userEditRequestDto->getChanges()->getPassword()) {
            $errorMessage .= $this->validateItem(
                $userEditRequestDto->getChanges()->getPassword(),
                [
                    $notBlankValidator
                ],
                'validation.password'
            );
        }

        if (null !== $userEditRequestDto->getChanges()->getFirstName()) {
            $errorMessage .= $this->validateItem(
                $userEditRequestDto->getChanges()->getFirstName(),
                [
                    $notBlankValidator
                ],
                'validation.first_name'
            );
        }

        if (null !== $userEditRequestDto->getChanges()->getLastName()) {
            $errorMessage .= $this->validateItem(
                $userEditRequestDto->getChanges()->getLastName(),
                [
                    $notBlankValidator
                ],
                'validation.last_name'
            );
        }

        if (!empty($errorMessage)) {
            throw new \InvalidArgumentException($errorMessage);
        }
    }

    /**
     * @param UserRequestDto $userRequestDto
     * @return array
     * @throws UserNotFoundException
     */
    public function loginUser(UserRequestDto $userRequestDto): array
    {
        $this->validateLoginRequest($userRequestDto);
        $user = $this->getUserByEmailAndPassword($userRequestDto->getEmail(), $userRequestDto->getPassword());

        return $this->formatUserForResponse($user);
    }

    /**
     * @param UserRequestDto $userRequestDto
     */
    private function validateLoginRequest(UserRequestDto $userRequestDto): void
    {
        $errorMessage = '';

        $emailValidator = new Email();
        $notBlankValidator = new NotBlank();

        $errorMessage .= $this->validateItem(
            $userRequestDto->getEmail(),
            [
                $emailValidator,
                $notBlankValidator
            ],
            'validation.email'
        );

        $errorMessage .= $this->validateItem(
            $userRequestDto->getPassword(),
            [
                $notBlankValidator
            ],
            'validation.password'
        );

        if (!empty($errorMessage)) {
            throw new \InvalidArgumentException($errorMessage);
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @param bool $throwException
     * @return User
     * @throws UserNotFoundException
     */
    private function getUserByEmailAndPassword(string $email, string $password, bool $throwException = true): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->doctrine->getRepository('App:User');
        /** @var User $user */
        $user = $userRepository->findOneBy(
            [
                'email' => $email,
                'password' => $password
            ]
        );

        if ($user === null && $throwException) {
            throw new UserNotFoundException($this->translator->trans('login.user_not_found'));
        }

        return $user;
    }

    /**
     * @param User $user
     * @param bool $includePassword
     * @return array
     */
    public function formatUserForResponse(User $user, bool $includePassword = false): array
    {
        $formattedUser = [
            'userId' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => ['ROLE_USER', 'ROLE_ADMIN']
        ];

        if ($includePassword) {
            $formattedUser['password'] = $user->getPassword();
            $formattedUser['salt'] = null;
        }

        return $formattedUser;
    }


    /**
     * @param UserRequestDto $userRequestDto
     * @return array
     * @throws UserAlreadyExistsException
     */
    public function registerUser(UserRequestDto $userRequestDto): array
    {
        $this->validateRegisterRequest($userRequestDto);

        if ($this->checkUserAlreadyExists($userRequestDto->getEmail())) {
            throw new UserAlreadyExistsException($this->translator->trans('register.user_already_exists'));
        }

        $user = $this->createUserByRequest($userRequestDto);

        return $this->formatUserForResponse($user);
    }

    /**
     * @param UserRequestDto $userRequestDto
     * @return User
     */
    private function createUserByRequest(UserRequestDto $userRequestDto): User
    {
        $user = (new User())
            ->setEmail($userRequestDto->getEmail())
            ->setPassword($userRequestDto->getPassword())
            ->setFirstName($userRequestDto->getFirstName())
            ->setLastName($userRequestDto->getLastName())
            ->setRole(User::ROLE_USER)
            ->setStatus(User::STATUS_ACTIVE);

        // Insert the above created user in database
        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        return $user;
    }

    private function editUserByRequest(User $user, UserChangesDto $userChangesDto): User
    {
        if (null !== $userChangesDto->getFirstName()) {
            $user->setFirstName($userChangesDto->getFirstName());
        }

        if (null !== $userChangesDto->getLastName()) {
            $user->setLastName($userChangesDto->getLastName());
        }

        if (null !== $userChangesDto->getPassword()) {
            $user->setPassword($userChangesDto->getPassword());
        }

        // Insert the above created user in database
        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        return $user;
    }

    /**
     * @param string $email
     * @return bool
     */
    private function checkUserAlreadyExists(string $email): bool
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->doctrine->getRepository('App:User');
        /** @var User[] $user */
        $users = $userRepository->findBy(
            [
                'email' => $email
            ]
        );

        return \count($users) > 0;
    }

    /**
     * @param UserRequestDto $userRequestDto
     */
    private function validateRegisterRequest(UserRequestDto $userRequestDto): void
    {
        $errorMessage = '';

        $emailValidator = new Email();
        $notBlankValidator = new NotBlank();

        $errorMessage .= $this->validateItem(
            $userRequestDto->getEmail(),
            [
                $emailValidator,
                $notBlankValidator
            ],
            'validation.email'
        );

        $errorMessage .= $this->validateItem(
            $userRequestDto->getPassword(),
            [
                $notBlankValidator
            ],
            'validation.password'
        );

        $errorMessage .= $this->validateItem(
            $userRequestDto->getFirstName(),
            [
                $notBlankValidator
            ],
            'validation.first_name'
        );

        $errorMessage .= $this->validateItem(
            $userRequestDto->getLastName(),
            [
                $notBlankValidator
            ],
            'validation.last_name'
        );

        if (!empty($errorMessage)) {
            throw new \InvalidArgumentException($errorMessage);
        }
    }

    /**
     * @param string $email
     * @return User
     * @throws UserNotFoundException
     */
    public function getUserFromDatabase(string $email): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->doctrine->getRepository('App:User');
        /** @var User $user */
        $user = $userRepository->findOneBy(
            [
                'email' => $email
            ]
        );

        if ($user === null) {
            throw new UserNotFoundException($this->translator->trans('login.user_not_found'));
        }
        return $user;
    }

    /**
     * @param string $email
     * @return Token
     * @throws UserNotFoundException
     */
    public function getTokenForEmail(string $email): Token
    {
        $errorMessage = $this->validateItem($email, [new NotBlank()], 'validation.email');
        if (!empty($errorMessage)) {
            throw new \InvalidArgumentException($errorMessage);
        }

        $user = $this->getUserFromDatabase($email);

        $tokenString = UtilsService::generateRandomToken($user->getEmail());
        return $this->createTokenForUser($tokenString, $user);
    }

    /**
     * @param $tokenString
     * @param $user
     * @return Token
     */
    public function createTokenForUser($tokenString, $user): Token
    {
        $entityManager = $this->doctrine->getManager();
        $token = (new Token())
            ->setToken($tokenString)
            ->setType(Token::TYPE_RESET_PASSWORD)
            ->setUser($user)
            ->setStatus(Token::STATUS_ACTIVE);

        $entityManager->persist($token);
        $entityManager->flush();

        return $token;
    }
}
