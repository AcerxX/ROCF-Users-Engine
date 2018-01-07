<?php
/**
 * Created by PhpStorm.
 * User: alexa
 * Date: 07.01.2018
 * Time: 00:46
 */

namespace App\Service;


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

    public function __construct(RegistryInterface $doctrine, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
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
        $locale = $loginInformation['locale'] ?? null;

        if ($locale !== null) {
            $this->translator->setLocale($locale);
        }

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
}