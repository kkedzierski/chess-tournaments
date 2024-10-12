<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Ui\Authentication\ResendConfirmationEmail;

use App\Account\Application\CreateUserService;
use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Ui\Authentication\ResendConfirmationEmail\ResendConfirmationEmailController;
use App\Account\Ui\Authentication\ResendConfirmationEmail\ResendConfirmationFormType;
use App\Kernel\Flasher\FlasherInterface;
use App\Tests\Unit\ConsecutiveParamsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ResendConfirmationEmailControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    private MockObject&CreateUserService $createUserService;

    private MockObject&FlasherInterface $flasher;

    private MockObject&ContainerInterface $container;

    private MockObject&FormInterface $form;

    private MockObject&Request $request;

    private MockObject&FormFactoryInterface $formFactory;

    private MockObject&RouterInterface $router;

    private MockObject&Environment $twig;

    private ResendConfirmationEmailController $controller;

    protected function setUp(): void
    {
        $this->createUserService = $this->createMock(CreateUserService::class);
        $this->flasher = $this->createMock(FlasherInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->controller = new ResendConfirmationEmailController($this->createUserService, $this->flasher);
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
            ->with(ResendConfirmationFormType::class)
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

    public function testCatchEmailRequiredExceptionWhenEmailNotFound(): void
    {
        $this->testValidSubmittedForm();

        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn(null);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'exception.emailRequired',
                'dashboard.authentication.resendConfirmation.error.emailRequired.title'
            );
        $this->createUserService
            ->expects($this->never())
            ->method('resendConfirmationEmail');

        $this->controller->resendConfirmationEmail($this->request);
    }

    public function testCatchEmailRequiredExceptionWhenEmailIsNotString(): void
    {
        $this->testValidSubmittedForm();

        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn(123);
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'exception.emailRequired',
                'dashboard.authentication.resendConfirmation.error.emailRequired.title'
            );
        $this->createUserService
            ->expects($this->never())
            ->method('resendConfirmationEmail');

        $this->controller->resendConfirmationEmail($this->request);
    }

    public function testCatchTokenGeneratingFailedException(): void
    {
        $this->testValidSubmittedForm();

        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('email');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'exception.tokenGeneratingFailed',
                'dashboard.authentication.resendConfirmation.error.tokenNotFound.title'
            );
        $this->createUserService
            ->expects($this->once())
            ->method('resendConfirmationEmail')
            ->with('email')
            ->willThrowException(new TokenGeneratingFailedException());

        $this->controller->resendConfirmationEmail($this->request);
    }

    public function testCatchCannotSendEmailException(): void
    {
        $this->testValidSubmittedForm();

        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('email');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'exception.cannotSendEmail',
                'dashboard.authentication.resendConfirmation.error.cannotSendEmail.title'
            );
        $this->createUserService
            ->expects($this->once())
            ->method('resendConfirmationEmail')
            ->with('email')
            ->willThrowException(new CannotSendEmailException());

        $this->controller->resendConfirmationEmail($this->request);
    }

    public function testCatchUnknownException(): void
    {
        $this->testValidSubmittedForm();

        $this->form
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('email');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with(
                'dashboard.authentication.register.resendConfirmation.error.description',
                'dashboard.authentication.register.resendConfirmation.error.title'
            );
        $this->createUserService
            ->expects($this->once())
            ->method('resendConfirmationEmail')
            ->with('email')
            ->willThrowException(new \Exception());

        $this->controller->resendConfirmationEmail($this->request);
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
            ->with(ResendConfirmationFormType::class)
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
            ->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturnSelf();
        $this->form
            ->expects($this->once())
            ->method('getData')
            ->willReturn('email');
        $this->createUserService
            ->expects($this->once())
            ->method('resendConfirmationEmail')
            ->with('email');
        $this->flasher
            ->expects($this->never())
            ->method('error');
        $this->flasher
            ->expects($this->once())
            ->method('success')
            ->with(
                'dashboard.authentication.resendConfirmation.success.description',
                'dashboard.authentication.resendConfirmation.success.title'
            );
        $this->router->expects($this->once())
            ->method('generate')
            ->with('app_login')
            ->willReturn('app_login');

        $this->controller->resendConfirmationEmail($this->request);
    }

    public function testReturnToRegisterRouteWhenFormIsNotSubmitted(): void
    {
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['form.factory'], ['twig']))
            ->willReturnOnConsecutiveCalls($this->formFactory, $this->twig);
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(ResendConfirmationFormType::class)
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->request)
            ->willReturnSelf();
        $this->form
            ->expects($this->exactly(2))
            ->method('isSubmitted')
            ->willReturn(false);
        $this->form
            ->expects($this->never())
            ->method('isValid')
            ->willReturn(true);
        $this->createUserService
            ->expects($this->never())
            ->method('resendConfirmationEmail')
            ->with('email');
        $this->flasher
            ->expects($this->never())
            ->method('error');
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('twig')
            ->willReturn(true);
        $this->form
            ->expects($this->once())
            ->method('createView')
            ->willReturn($formView = $this->createMock(FormView::class));
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with(
                'dashboard/authentication/registration/resend-confirmation-email.twig',
                [
                    'resendConfirmationForm'     => $formView,
                ]
            )->willReturn('content');

        $response = $this->controller->resendConfirmationEmail($this->request);

        $this->assertSame('content', $response->getContent());
    }

    public function testReturnToRegisterRouteWhenFormIsNotValid(): void
    {
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['form.factory'], ['twig']))
            ->willReturnOnConsecutiveCalls($this->formFactory, $this->twig);
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(ResendConfirmationFormType::class)
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
            ->willReturn(false);
        $this->createUserService
            ->expects($this->never())
            ->method('resendConfirmationEmail')
            ->with('email');
        $this->flasher
            ->expects($this->never())
            ->method('error');
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('twig')
            ->willReturn(true);
        $this->form
            ->expects($this->once())
            ->method('createView')
            ->willReturn($formView = $this->createMock(FormView::class));
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with(
                'dashboard/authentication/registration/resend-confirmation-email.twig',
                [
                    'resendConfirmationForm'     => $formView,
                ]
            )->willReturn('content');

        $response = $this->controller->resendConfirmationEmail($this->request);

        $this->assertSame('content', $response->getContent());
    }
}
