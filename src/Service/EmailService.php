<?php

namespace App\Service;

use App\Dto\UserChangesDto;
use App\Dto\UserEditRequestDto;
use App\Dto\UserRequestDto;
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

class EmailService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

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
     * @param \Swift_Mailer $mailer
     * @param TranslatorInterface $translator
     * @param string $_locale
     */
    public function __construct(\Swift_Mailer $mailer, TranslatorInterface $translator, string $_locale)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->locale = $_locale;

        $this->translator->setLocale($this->locale);
    }

    /**
     * @param string $recipientEmail
     * @param string $subject
     * @param string $emailBody
     */
    public function sendEmail(string $recipientEmail, string $subject, string $emailBody): void
    {
        $message = (new \Swift_Message($subject))
            ->setFrom('roprojectstest@gmail.com')
            ->setTo($recipientEmail)
            ->setBody($emailBody, 'text/html')
            ->addPart($emailBody, 'text/plain');

        $this->mailer->send($message);
    }
}
