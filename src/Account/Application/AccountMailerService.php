<?php

declare(strict_types=1);

namespace App\Account\Application;

use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Domain\PasswordToken;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountMailerService
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $companyName = '',
        private readonly string $companyEmail = '',
    ) {
    }

    /**
     * @throws CannotSendEmailException
     */
    public function sendRegistrationConfirmationEmail(string $email, PasswordToken $passwordToken): void
    {
        $this->sendEmailToUser(
            $email,
            'dashboard.authentication.register.confirmation.title',
            'dashboard/authentication/registration/confirmation-email-template.html.twig',
            [
                'token' => $passwordToken->getToken(),
            ],
            'An error occurred while sending registration confirmation email.'
        );
    }

    /**
     * @throws CannotSendEmailException
     */
    public function sendResetPasswordEmail(string $email, PasswordToken $passwordToken): void
    {
        $this->sendEmailToUser(
            $email,
            'dashboard.authentication.resetPassword.title',
            'dashboard/authentication/resetPassword/reset-password-email-template.html.twig',
            [
                    'token'      => $passwordToken->getToken(),
                    'emailValue' => $email,
                ],
            'An error occurred while sending reset password email.'
        );
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws CannotSendEmailException
     */
    private function sendEmailToUser(
        string $email,
        string $title,
        string $twigTemplatePath,
        array  $context = [],
        string $logReason = 'An error occurred while sending email.'
    ): void {
        if (empty($this->companyEmail) || empty($this->companyName)) {
            throw new \LogicException('Company data is missing, please check CT_NAME, CT_EMAIL env configuration variables.');
        }
        $context['companyName'] = $this->companyName;

        $emailToSend = (new TemplatedEmail())
            ->from(new Address($this->companyEmail, $this->companyName))
            ->to(new Address($email))
            ->subject($this->translator->trans($title))
            ->htmlTemplate($twigTemplatePath)
            ->context($context);

        try {
            $this->mailer->send($emailToSend);
        } catch (\Throwable $exception) {
            $this->logger->critical(
                $logReason,
                [
                    'exception' => $exception,
                    'email'     => $email,
                    'class'     => __CLASS__,
                ]
            );
            throw new CannotSendEmailException();
        }
    }
}
