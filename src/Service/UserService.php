<?php

namespace App\Service;

use App\Entity\User;
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
     * @param string $locale
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function setLocaleForTranslator(string $locale): void
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
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function __construct(RegistryInterface $doctrine, TranslatorInterface $translator, string $_locale)
    {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $this->locale = $_locale;

        $this->translator->setLocale($this->locale);
    }

    /**
     * @param array $loginInformation
     * @return array
     * @throws \Symfony\Component\Validator\Exception\MissingOptionsException
     * @throws \Symfony\Component\Validator\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function validateLogin(array $loginInformation): array
    {
        $errors = [];
        $validator = Validation::createValidator();

        $email = $loginInformation['email'] ?? '';
        $password = $loginInformation['password'] ?? '';

        $emailViolation = $validator->validate(
            $email,
            [
                new Email(),
                new NotBlank()
            ]
        );
        $emailString = $this->translator->trans('validation_email');
        foreach ($emailViolation as $violation) {
            $errors[] = $emailString . $this->translator->trans($violation->getMessage());
        }


        $passwordViolation = $validator->validate(
            $password,
            [
                new NotBlank()
            ]
        );
        $passwordString = $this->translator->trans('validation_password');
        foreach ($passwordViolation as $violation) {
            $errors[] = $passwordString . $this->translator->trans($violation->getMessage());
        }

        return $errors;
    }

    /**
     * @param string $email
     * @param string $password
     * @return User
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws UserNotFoundException
     */
    public function getUserByEmailAndPassword(string $email, string $password): User
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

        if ($user === null) {
            throw new UserNotFoundException($this->translator->trans('login.user_not_found'));
        }

        return $user;
    }

    /**
     * @param User $user
     * @return array
     */
    public function formatUserForLoginResponse(User $user): array
    {
        $formattedUser = [
            'userId' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'role' => $user->getRole()->getRole()
        ];

        return $formattedUser;
    }
}