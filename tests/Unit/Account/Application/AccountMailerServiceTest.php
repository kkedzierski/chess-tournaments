<?php

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\AccountMailerService;
use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Domain\PasswordToken;
use App\Account\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountMailerServiceTest extends TestCase
{
    private MockObject&TranslatorInterface $translator;
    private MockObject&MailerInterface $mailer;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    private function generateService(string $companyName = '', string $companyEmail = ''): AccountMailerService
    {
        return new AccountMailerService($this->translator, $this->mailer, $this->logger, $companyName, $companyEmail);
    }

    public function testThrowLogicExceptionWhenCompanyEmailNotFoundOnResetPasswordEmail(): void
    {
        $user = new User();
        $token = new PasswordToken($user);
        $service = $this->generateService(companyName: 'Company Name');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Company data is missing, please check CT_NAME, CT_EMAIL env configuration variables.');

        $this->mailer
            ->expects($this->never())
            ->method('send');

        $service->sendResetPasswordEmail('email', $token);
    }

    public function testThrowLogicExceptionWhenCompanyNameNotFoundOnResetPasswordEmail(): void
    {
        $user = new User();
        $token = new PasswordToken($user);
        $service = $this->generateService(companyEmail: 'email');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Company data is missing, please check CT_NAME, CT_EMAIL env configuration variables.');

        $this->mailer
            ->expects($this->never())
            ->method('send');

        $service->sendResetPasswordEmail('email', $token);
    }

    public function testLogAndThrowExceptionWhenUserSendingMailFailsOnResetPassword(): void
    {
        $user = new User();
        $token = new PasswordToken($user, token: 'token');
        $service = $this->generateService('name', 'test@example.com');

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('dashboard.authentication.resetPassword.title')
            ->willReturn('translated text');
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                static fn (TemplatedEmail $email) =>
                    $email->getFrom()[0]->getName() === 'name'
                    && $email->getFrom()[0]->getAddress() === 'test@example.com'
                    && $email->getTo()[0]->getAddress() === 'test2@example.com'
                    && $email->getSubject() === 'translated text'
                    && $email->getHtmlTemplate() === 'dashboard/authentication/resetPassword/reset-password-email-template.html.twig'
                    && $email->getContext() === ['token' => 'token', 'emailValue' => 'test2@example.com', 'companyName' => 'name']
            ))->willThrowException($exception = new \Exception());
        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with(
                'An error occurred while sending reset password email.',
                [
                    'exception' => $exception,
                    'email' => 'test2@example.com',
                    'class' => AccountMailerService::class,
                ]
            );

        $this->expectException(CannotSendEmailException::class);

        $service->sendResetPasswordEmail('test2@example.com', $token);
    }

    public function testLogAndThrowExceptionWhenUserSendingMailFailsOnConfirmRegistration(): void
    {
        $user = new User();
        $token = new PasswordToken($user, token: 'token');
        $service = $this->generateService('name', 'test@example.com');

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('dashboard.authentication.register.confirmation.title')
            ->willReturn('translated text');
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                static fn (TemplatedEmail $email) =>
                    $email->getFrom()[0]->getName() === 'name'
                    && $email->getFrom()[0]->getAddress() === 'test@example.com'
                    && $email->getTo()[0]->getAddress() === 'test2@example.com'
                    && $email->getSubject() === 'translated text'
                    && $email->getHtmlTemplate() === 'dashboard/authentication/registration/confirmation-email-template.html.twig'
                    && $email->getContext() === ['token' => 'token', 'companyName' => 'name']
            ))->willThrowException($exception = new \Exception());
        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with(
                'An error occurred while sending registration confirmation email.',
                [
                    'exception' => $exception,
                    'email' => 'test2@example.com',
                    'class' => AccountMailerService::class,
                ]
            );

        $this->expectException(CannotSendEmailException::class);

        $service->sendRegistrationConfirmationEmail('test2@example.com', $token);
    }
}
