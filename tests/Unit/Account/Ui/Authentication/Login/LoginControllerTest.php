<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Ui\Authentication\Login;

use App\Account\Domain\User;
use App\Account\Ui\Authentication\Login\LoginController;
use App\Account\Ui\Authentication\Login\LoginFormType;
use App\Kernel\Flasher\FlasherInterface;
use App\Tests\Unit\ConsecutiveParamsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class LoginControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    private MockObject&AuthenticationUtils $authenticationUtils;

    private MockObject&FlasherInterface $flasher;

    private MockObject&ContainerInterface $container;

    private MockObject&TokenInterface $token;

    private MockObject&TokenStorageInterface $tokenStorage;

    private MockObject&FormFactoryInterface $formFactory;

    private MockObject&FormInterface $form;

    private MockObject&Environment $twig;

    private MockObject&RouterInterface $router;

    private LoginController $loginController;

    protected function setUp(): void
    {
        $this->authenticationUtils = $this->createMock(AuthenticationUtils::class);
        $this->flasher = $this->createMock(FlasherInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->token = $this->createMock(TokenInterface::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->tokenStorage = $this->createMock(TokenStorage::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->loginController = new LoginController();
        $this->loginController->setContainer($this->container);
    }

    public function testShowMustVerifyEmailToLogin(): void
    {
        $user = new User();

        $this->container
            ->expects($this->exactly(2))
            ->method('has')
            ->with(...$this->consecutiveParams(['security.token_storage'], ['twig']))
            ->willReturn(true);
        $this->container
            ->expects($this->exactly(4))
            ->method('get')
            ->with(...$this->consecutiveParams(
                ['security.token_storage'],
                ['router'],
                ['form.factory'],
                ['twig'],
            ))
            ->willReturnOnConsecutiveCalls($this->tokenStorage, $this->router, $this->formFactory, $this->twig);
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);
        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->authenticationUtils
            ->expects($this->once())
            ->method('getLastAuthenticationError')
            ->willReturn(null);
        $this->router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/register/resend-confirmation-email');
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('dashboard.authentication.login.verifyEmail', null, [], ['%resend_url%' => '/register/resend-confirmation-email']);
        $this->authenticationUtils
            ->expects($this->once())
            ->method('getLastUsername')
            ->willReturn('username');
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(LoginFormType::class)
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('createView')
            ->willReturn($formView = $this->createMock(FormView::class));
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with(
                'dashboard/authentication/login/login.html.twig',
                [
                    'last_username' => 'username',
                    'error'         => null,
                    'loginForm'     => $formView,
                ]
            )->willReturn('content');

        $response = $this->loginController->login($this->authenticationUtils, $this->flasher);

        $this->assertSame('content', $response->getContent());
    }

    public function testRedirectToDashboardRouteWhenUserIsAdminAndIsVerified(): void
    {
        $user = new User();
        $user->makeAdmin();
        $user->verify();

        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(
                ['security.token_storage'],
                ['router'],
            ))
            ->willReturnOnConsecutiveCalls($this->tokenStorage, $this->router);
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);
        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('dashboard')
            ->willReturn('/dashboard');

        $redirectResponse = $this->loginController->login($this->authenticationUtils, $this->flasher);
        $this->assertStringContainsString('Redirecting to <a href="/dashboard">/dashboard</a>.', $redirectResponse->getContent());
    }

    public function testThrowLogicExceptionWhenLogout(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This method can be blank - it will be intercepted by the logout key on your firewall.');

        $this->loginController->logout();
    }
}
