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
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
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
     * @var Translator
     */
    protected $translator;

    public function __construct(RegistryInterface $doctrine, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
    }

    /**
     * @param null|string $email
     * @param null|string $password
     * @param null|string $ipAddress
     * @param null|string $locale
     * @return array
     * @throws \Symfony\Component\Validator\Exception\MissingOptionsException
     * @throws \Symfony\Component\Validator\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function validateLogin(?string $email, ?string $password, ?string $ipAddress, ?string $locale)
    {
        $errors = [];
        $validator = Validation::createValidator();

        $emailViolation = $validator->validate(
            $email,
            [
                new Email(),
                new NotBlank()
            ]
        );
        foreach ($emailViolation as $violation) {
            $errors[] = $this->translator->trans($violation->getMessage(), [], [], $locale);
        }


        $passwordViolation = $validator->validate(
            $password,
            [
                new NotBlank()
            ]
        );
        foreach ($passwordViolation as $violation) {
            $errors[] = $this->translator->trans($violation->getMessage(), [], [], $locale);
        }


        $ipAddressViolation = $validator->validate(
            $ipAddress,
            [
                new NotBlank()
            ]
        );
        foreach ($ipAddressViolation as $violation) {
            $errors[] = $this->translator->trans($violation->getMessage(), [], [], $locale);
        }


        $localeViolation = $validator->validate($locale, [
            new NotBlank()
        ]);
        foreach ($localeViolation as $violation) {
            $errors[] = $this->translator->trans($violation->getMessage(), [], [], $locale);
        }

        return $errors;
    }
}