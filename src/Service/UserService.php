<?php

namespace App\Service;

use App\Dto\UserRequestDto;
use App\Entity\Role;
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
    protected $doctrine;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $locale;

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
        $validator = Validation::createValidator();

        $emailViolation = $validator->validate(
            $userRequestDto->getEmail(),
            [
                new Email(),
                new NotBlank()
            ]
        );
        $emailString = $this->translator->trans('validation.email');
        foreach ($emailViolation as $violation) {
            $errorMessage .= $emailString . ' ' . $this->translator->trans($violation->getMessage()) . ' ';
        }


        $passwordViolation = $validator->validate(
            $userRequestDto->getPassword(),
            [
                new NotBlank()
            ]
        );
        $passwordString = $this->translator->trans('validation.password');
        foreach ($passwordViolation as $violation) {
            $errorMessage .= $passwordString . ' ' . $this->translator->trans($violation->getMessage()) . ' ';
        }

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
     * @return array
     */
    public function formatUserForResponse(User $user): array
    {
        $formattedUser = [
            'userId' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'role' => $user->getRole()
        ];

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
            ->setRole(User::ROLE_USER);

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
        $validator = Validation::createValidator();

        $emailViolation = $validator->validate(
            $userRequestDto->getEmail(),
            [
                new Email(),
                new NotBlank()
            ]
        );
        $emailString = $this->translator->trans('validation.email');
        foreach ($emailViolation as $violation) {
            $errorMessage .= $emailString . ' ' .  $this->translator->trans($violation->getMessage()) . ' ';
        }


        $passwordViolation = $validator->validate(
            $userRequestDto->getPassword(),
            [
                new NotBlank()
            ]
        );
        $passwordString = $this->translator->trans('validation.password');
        foreach ($passwordViolation as $violation) {
            $errorMessage .= $passwordString . ' ' . $this->translator->trans($violation->getMessage()) . ' ';
        }


        $firstNameViolation = $validator->validate(
            $userRequestDto->getFirstName(),
            [
                new NotBlank()
            ]
        );
        $firstNameString = $this->translator->trans('validation.first_name');
        foreach ($firstNameViolation as $violation) {
            $errorMessage .= $firstNameString . ' ' . $this->translator->trans($violation->getMessage()) . ' ';
        }


        $lastNameViolation = $validator->validate(
            $userRequestDto->getLastName(),
            [
                new NotBlank()
            ]
        );
        $lastNameString = $this->translator->trans('validation.last_name');
        foreach ($lastNameViolation as $violation) {
            $errorMessage .= $lastNameString . ' ' . $this->translator->trans($violation->getMessage()) . ' ';
        }

        if (!empty($errorMessage)) {
            throw new \InvalidArgumentException($errorMessage);
        }
    }
}