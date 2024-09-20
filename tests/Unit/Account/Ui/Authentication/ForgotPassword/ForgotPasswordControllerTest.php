<?php

namespace App\Tests\Unit\Account\Ui\Authentication\ForgotPassword;

use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Application\Password\ResetPasswordService;
use App\Account\Ui\Authentication\ForgotPassword\ForgotPasswordController;
use App\Account\Ui\Authentication\ForgotPassword\ForgotPasswordFormType;
use App\Kernel\Flasher\FlasherInterface;
use App\Tests\Unit\ConsecutiveParamsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ForgotPasswordControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    private MockObject&ResetPasswordService $resetPasswordService;
    private MockObject&FlasherInterface $flasher;

    private MockObject&Request $request;

    private MockObject&ContainerInterface $container;

    private MockObject&FormInterface $form;

    private MockObject&FormFactoryInterface $formFactory;

    private MockObject&Environment $twig;

    private MockObject&RouterInterface $router;

    private ForgotPasswordController $controller;

    protected function setUp(): void
    {
        $this->resetPasswordService = $this->createMock(ResetPasswordService::class);
        $this->flasher = $this->createMock(FlasherInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->controller = new ForgotPasswordController($this->resetPasswordService, $this->flasher);
        $this->controller->setContainer($this->container);
    }

    private function testValidSubmittedForm(): void
    {
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['form.factory'], ['twig']))
            ->willReturnOnConsecutiveCalls($this->formFactory, $this->twig);
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(ForgotPasswordFormType::class)
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->request)
            ->willReturnSelf();
        $this->form
            ->expects($this->exactly(2))
            ->method('isSubmitted')
            ->willReturn(true);
        $this->form
            ->expects($this->exactly(2))
            ->method('isValid')
            ->willReturn(true);
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('twig')
            ->willReturn(true);
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->willReturn('rendered');
    }

    public function testThrowEmailRequiredExceptionWhenEmailNotString(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn(123);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.emailRequired', 'dashboard.authentication.resetPassword.email.error.emailRequired.title')
            ->willReturnSelf();

        $this->controller->forgotPassword($this->request);
    }

    public function testThrowEmailRequiredExceptionWhenEmailNotFound(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn(null);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.emailRequired', 'dashboard.authentication.resetPassword.email.error.emailRequired.title')
            ->willReturnSelf();

        $this->controller->forgotPassword($this->request);
    }

    public function testThrowExceptionWhenTokenGeneratingFailed(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('email');
        $this->resetPasswordService
            ->expects($this->once())
            ->method('processResetPasswordSendEmail')
            ->with('email')
            ->willThrowException(new TokenGeneratingFailedException());
        $this->flasher
            ->expects($this->never())
            ->method('success');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.tokenGeneratingFailed', 'dashboard.authentication.resetPassword.email.error.tokenGenerating.title')
            ->willReturnSelf();

        $this->controller->forgotPassword($this->request);
    }

    public function testThrowExceptionWhenCannotSendEmail(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('email');
        $this->resetPasswordService
            ->expects($this->once())
            ->method('processResetPasswordSendEmail')
            ->with('email')
            ->willThrowException(new CannotSendEmailException());
        $this->flasher
            ->expects($this->never())
            ->method('success');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'exception.cannotSendEmail',
                'dashboard.authentication.resetPassword.email.error.cannotSendEmail.title'
            )->willReturnSelf();

        $this->controller->forgotPassword($this->request);
    }

    public function testThrowExceptionWhenUnknownError(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('email');
        $this->resetPasswordService
            ->expects($this->once())
            ->method('processResetPasswordSendEmail')
            ->with('email')
            ->willThrowException(new \Exception());
        $this->flasher
            ->expects($this->never())
            ->method('success');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'dashboard.authentication.resetPassword.email.error.description',
                'dashboard.authentication.resetPassword.email.error.title'
            )->willReturnSelf();

        $this->controller->forgotPassword($this->request);
    }

    public function testRedirectToLoginWhenResetPasswordSendEmailSuccess(): void
    {
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('email');
        $this->resetPasswordService
            ->expects($this->once())
            ->method('processResetPasswordSendEmail')
            ->with('email');
        $this->flasher
            ->expects($this->once())
            ->method('success')
            ->with(
                'dashboard.authentication.resetPassword.email.sent.description',
                'dashboard.authentication.resetPassword.email.sent.title'
            );
        $this->flasher
            ->expects($this->never())
            ->method('error');
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['form.factory'], ['router']))
            ->willReturnOnConsecutiveCalls($this->formFactory, $this->router);
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(ForgotPasswordFormType::class)
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->request)
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->router->expects($this->once())
            ->method('generate')
            ->with('app_login')
            ->willReturn('app_login');

        $this->controller->forgotPassword($this->request);
    }
}
