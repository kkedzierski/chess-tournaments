<?php

namespace App\Tests\Unit\Account\Ui\Authentication\Registration;

use App\Account\Application\CreateUserService;
use App\Account\Domain\User;
use App\Account\Ui\Authentication\AccountAuthenticator;
use App\Account\Ui\Authentication\Registration\RegistrationController;
use App\Account\Ui\Authentication\Registration\RegistrationFormType;
use App\Kernel\Flasher\FlasherInterface;
use App\Tests\Unit\ConsecutiveParamsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class RegistrationControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    private MockObject&CreateUserService $createUserService;
    private MockObject&FlasherInterface $flasher;

    private MockObject&Request $request;

    private MockObject&ContainerInterface $container;

    private MockObject&FormInterface $form;

    private MockObject&FormFactoryInterface $formFactory;

    private MockObject&Environment $twig;

    private MockObject&RouterInterface $router;

    private MockObject&Security $security;

    private RegistrationController $controller;

    protected function setUp(): void
    {
        $this->createUserService = $this->createMock(CreateUserService::class);
        $this->flasher = $this->createMock(FlasherInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->security = $this->createMock(Security::class);

        $this->controller = new RegistrationController($this->createUserService, $this->flasher);
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
            ->with(RegistrationFormType::class)
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
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['email'], ['password']))
            ->willReturnSelf();
        $this->form
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(123, 'password');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.emailRequired', 'dashboard.authentication.register.email.error.emailRequired.title')
            ->willReturnSelf();

        $this->controller->register($this->request);
    }

    public function testThrowEmailRequiredExceptionWhenEmailNotFound(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['email'], ['password']))
            ->willReturnSelf();
        $this->form
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(null, 'password');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.emailRequired', 'dashboard.authentication.register.email.error.emailRequired.title')
            ->willReturnSelf();

        $this->controller->register($this->request);
    }

    public function testThrowPasswordRequiredExceptionWhenPasswordNotString(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['email'], ['password']))
            ->willReturnSelf();
        $this->form
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls('email', 123);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.passwordRequired', 'dashboard.authentication.register.password.error.passwordRequired.title')
            ->willReturnSelf();

        $this->controller->register($this->request);
    }

    public function testThrowPasswordRequiredExceptionWhenPasswordNotFound(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['email'], ['password']))
            ->willReturnSelf();
        $this->form
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls('email', null);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.passwordRequired', 'dashboard.authentication.register.password.error.passwordRequired.title')
            ->willReturnSelf();

        $this->controller->register($this->request);
    }

    public function testThrowExceptionWhenCreatingUserFailed(): void
    {
        $this->testValidSubmittedForm();
        $this->form
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['email'], ['password']))
            ->willReturnSelf();
        $this->form
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls('email', 'password');
        $this->createUserService
            ->expects($this->once())
            ->method('createUser')
            ->with('email', 'password')
            ->willThrowException(new \Exception());
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('dashboard.authentication.register.error.description', 'dashboard.authentication.register.error.title')
            ->willReturnSelf();

        $this->controller->register($this->request);
    }

    public function testRedirectToLoginWhenCreatingUserSuccess(): void
    {
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['form.factory'], ['router']))
            ->willReturnOnConsecutiveCalls($this->formFactory, $this->router);
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(RegistrationFormType::class)
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
        $this->form
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['email'], ['password']))
            ->willReturnSelf();
        $this->form
            ->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls('email', 'password');
        $this->createUserService
            ->expects($this->once())
            ->method('createUser')
            ->with('email', 'password')
            ->willReturn(new User());
        $this->flasher
            ->expects($this->never())
            ->method('error');
        $this->flasher
            ->expects($this->once())
            ->method('success')
            ->with(
                'dashboard.authentication.register.success.description',
                'dashboard.authentication.register.success.title'
            );
        $this->router->expects($this->once())
            ->method('generate')
            ->with('app_login')
            ->willReturn('app_login');

        $this->controller->register($this->request);
    }

    public function testThrowExceptionWhenTokenNotFoundOnConfirm(): void
    {
        $this->request
            ->expects($this->once())
            ->method('get')
            ->with("token")
            ->willReturn(null);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.tokenNotFound', 'dashboard.authentication.register.confirm.error.tokenNotFound.title');
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('router')
            ->willReturn($this->router);
        $this->router->expects($this->once())
            ->method('generate')
            ->with('app_register')
            ->willReturn('app_register');

        $this->controller->confirmRegistration($this->request, $this->security);
    }

    public function testThrowExceptionWhenTokenIsNotStringOnConfirm(): void
    {
        $this->request
            ->expects($this->once())
            ->method('get')
            ->with("token")
            ->willReturn(123);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.tokenNotFound', 'dashboard.authentication.register.confirm.error.tokenNotFound.title');
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('router')
            ->willReturn($this->router);
        $this->router->expects($this->once())
            ->method('generate')
            ->with('app_register')
            ->willReturn('app_register');

        $this->controller->confirmRegistration($this->request, $this->security);
    }

    public function testCatchExceptionWhenTokenVerifyByTokenOnConfigFailed(): void
    {
        $this->request
            ->expects($this->once())
            ->method('get')
            ->with("token")
            ->willReturn('token');
        $this->createUserService
            ->expects($this->once())
            ->method('verifyByToken')
            ->with('token')
            ->willThrowException(new \Exception());
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'dashboard.authentication.register.confirm.error.description',
                'dashboard.authentication.register.confirm.error.title'
            );
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('router')
            ->willReturn($this->router);
        $this->router->expects($this->once())
            ->method('generate')
            ->with('app_register')
            ->willReturn('app_register');

        $this->controller->confirmRegistration($this->request, $this->security);
    }

    public function testLoginUserWhenVerifyByTokenSuccessOnConfirm(): void
    {
        $this->request
            ->expects($this->once())
            ->method('get')
            ->with("token")
            ->willReturn('token');
        $this->createUserService
            ->expects($this->once())
            ->method('verifyByToken')
            ->with('token')
            ->willReturn($user = new User());
        $this->flasher
            ->expects($this->never())
            ->method('error');
        $this->flasher
            ->expects($this->once())
            ->method('success')
            ->with(
                'dashboard.authentication.register.confirm.success.description',
                'dashboard.authentication.register.confirm.success.title'
            );
        $this->security
            ->expects($this->once())
            ->method('login')
            ->with($user, AccountAuthenticator::class, 'main');

        $this->controller->confirmRegistration($this->request, $this->security);
    }
}
