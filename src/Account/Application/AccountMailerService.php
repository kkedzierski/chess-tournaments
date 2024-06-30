<?php

namespace App\Account\Application;

use App\Account\Application\Exception\CannotSendEmailException;
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
     * @param array<string, mixed> $context
     * @throws CannotSendEmailException
     */
    public function sendEmailToUser(
        string   $email,
        string $translationSubjectKey,
        string $twigTemplatePath,
        array  $context = [],
        string $logReason = 'An error occurred while sending email.'
    ): void {
        if (empty($this->companyEmail) || empty($this->companyName)) {
            throw new \LogicException('Company data is missing, please check CT_NAME, CT_EMAIL env configuration variables.');
        }
        $context['companyName'] = $this->companyName;

        /** @infection-ignore-all  */
        $email = (new TemplatedEmail())
            ->from(new Address($this->companyEmail, $this->companyName))
            ->to(new Address($email))
            ->subject($this->translator->trans($translationSubjectKey))
            ->htmlTemplate($twigTemplatePath)
            ->context($context);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->critical(
                $logReason,
                [
                    'exception' => $exception,
                    'email' => $email,
                    'class' => __CLASS__,
                ]
            );
            throw new CannotSendEmailException();
        }
    }
}
