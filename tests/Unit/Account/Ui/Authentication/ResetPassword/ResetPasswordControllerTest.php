<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Ui\Authentication\ResetPassword;

use App\Account\Application\Exception\ResetPasswordException;
use App\Account\Application\Password\ResetPasswordService;
use App\Account\Ui\Authentication\ResetPassword\ResetPasswordController;
use App\Account\Ui\Authentication\ResetPassword\ResetPasswordFormType;
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

class ResetPasswordControllerTest extends TestCase
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

    private ResetPasswordController $controller;

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

        $this->controller = new ResetPasswordController($this->resetPasswordService, $this->flasher);
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
            ->with(ResetPasswordFormType::class)
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

    public function testThrowPasswordRequiredExceptionWhenPasswordNotString(): void
    {
        $this->testValidSubmittedForm();
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls('token', 'email');
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn(123);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.passwordRequired', 'dashboard.authentication.resetPassword.error.passwordRequired.title')
            ->willReturnSelf();

        $this->controller->resetPassword($this->request);
    }

    public function testThrowPasswordRequiredExceptionWhenPasswordNotFound(): void
    {
        $this->testValidSubmittedForm();
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls('token', 'email');
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn(null);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.passwordRequired', 'dashboard.authentication.resetPassword.error.passwordRequired.title')
            ->willReturnSelf();

        $this->controller->resetPassword($this->request);
    }

    public function testThrowTokenNotFoundExceptionWhenTokenIsNotString(): void
    {
        $this->testValidSubmittedForm();
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls(123, 'email');
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('password');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.tokenNotFound', 'dashboard.authentication.resetPassword.error.tokenNotFound.title')
            ->willReturnSelf();

        $this->controller->resetPassword($this->request);
    }

    public function testThrowTokenNotFoundExceptionWhenTokenNotFound(): void
    {
        $this->testValidSubmittedForm();
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls(null, 'email');
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('password');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.tokenNotFound', 'dashboard.authentication.resetPassword.error.tokenNotFound.title')
            ->willReturnSelf();

        $this->controller->resetPassword($this->request);
    }

    public function testThrowUserNotFoundExceptionWhenEmailNotFound(): void
    {
        $this->testValidSubmittedForm();
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls('token', null);
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('password');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.userNotFound', 'dashboard.authentication.resetPassword.error.userNotFound.title')
            ->willReturnSelf();

        $this->controller->resetPassword($this->request);
    }

    public function testThrowUserNotFoundExceptionWhenEmailNotString(): void
    {
        $this->testValidSubmittedForm();
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls('token', 123);
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('password');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.userNotFound', 'dashboard.authentication.resetPassword.error.userNotFound.title')
            ->willReturnSelf();

        $this->controller->resetPassword($this->request);
    }

    public function testCatchResetPasswordExceptionWhenThrowsOnResetPassword(): void
    {
        $this->testValidSubmittedForm();
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls('token', 'email');
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('password');
        $this->resetPasswordService
            ->expects($this->once())
            ->method('resetPassword')
            ->with('token', 'email', 'password')
            ->willThrowException(new ResetPasswordException());
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.resetPassword', 'dashboard.authentication.resetPassword.error.resetPassword.title')
            ->willReturnSelf();

        $this->controller->resetPassword($this->request);
    }

    public function testCatchUnknownExceptionWhenThrowsOnResetPassword(): void
    {
        $this->testValidSubmittedForm();
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls('token', 'email');
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('password');
        $this->resetPasswordService
            ->expects($this->once())
            ->method('resetPassword')
            ->with('token', 'email', 'password')
            ->willThrowException(new \Exception());
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'dashboard.authentication.resetPassword.error.description',
                'dashboard.authentication.resetPassword.error.title'
            )
            ->willReturnSelf();

        $this->controller->resetPassword($this->request);
    }

    public function testRedirectToLoginWhenResetPasswordSuccess(): void
    {
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['form.factory'], ['router']))
            ->willReturnOnConsecutiveCalls($this->formFactory, $this->router);
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(ResetPasswordFormType::class)
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
        $this->request
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['token'], ['emailValue']))
            ->willReturnOnConsecutiveCalls('token', 'email');
        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('password')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('password');
        $this->resetPasswordService
            ->expects($this->once())
            ->method('resetPassword')
            ->with('token', 'email', 'password');
        $this->flasher
            ->expects($this->once())
            ->method('success')
            ->with(
                'dashboard.authentication.resetPassword.success.description',
                'dashboard.authentication.resetPassword.success.title'
            );
        $this->flasher
            ->expects($this->never())
            ->method('error');
        $this->router->expects($this->once())
            ->method('generate')
            ->with('app_login')
            ->willReturn('app_login');

        $this->controller->resetPassword($this->request);
    }
}
