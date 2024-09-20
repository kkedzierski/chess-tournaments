<?php

namespace App\Tests\Unit\Account\Ui\Authentication;

use App\Account\Application\AccountAuthenticatorService;
use App\Account\Ui\Authentication\AccountAuthenticator;
use App\Account\Ui\Exception\EmailRequiredException;
use App\Account\Ui\Exception\PasswordRequiredException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class AccountAuthenticatorTest extends TestCase
{
    private MockObject&AccountAuthenticatorService $accountAuthenticatorService;

    private MockObject&Request $request;

    private MockObject&SessionInterface $session;

    private MockObject&TokenInterface $token;

    private AccountAuthenticator $accountAuthenticator;

    protected function setUp(): void
    {
        $this->accountAuthenticatorService = $this->createMock(AccountAuthenticatorService::class);
        $this->request = $this->createMock(Request::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->token = $this->createMock(TokenInterface::class);

        $this->accountAuthenticator = new AccountAuthenticator($this->accountAuthenticatorService);
    }

    public function testNotSupportWhenLoginUrlIsNotInPathInfo(): void
    {
        $this->request->expects($this->once())
            ->method('isMethod')
            ->with('POST')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('/notLogin');
        $this->accountAuthenticatorService
            ->expects($this->once())
            ->method('getLoginUrl')
            ->willReturn('/login');

        $this->assertFalse($this->accountAuthenticator->supports($this->request));
    }

    public function testSupportWhenLoginUrlIsinPathInfo(): void
    {
        $this->request->expects($this->once())
            ->method('isMethod')
            ->with('POST')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('/login');
        $this->accountAuthenticatorService
            ->expects($this->once())
            ->method('getLoginUrl')
            ->willReturn('/login');

        $this->assertTrue($this->accountAuthenticator->supports($this->request));
    }

    public function testThrowEmailNotFoundExceptionWhenEmailNotFound(): void
    {
        $bag = new InputBag();
        $bag->set('login_form', []);
        $this->request->request = $bag;

        $this->expectException(EmailRequiredException::class);
        $this->expectExceptionMessage('exception.emailRequired');
        $this->expectExceptionCode(422);

        $this->request
            ->expects($this->never())
            ->method('getSession')
            ->willReturn($this->session);
        $this->session
            ->expects($this->never())
            ->method('set')
            ->with('_security.last_username', null);

        $this->accountAuthenticator->authenticate($this->request);
    }

    public function testThrowPasswordNotFoundExceptionWhenPasswordNotFound(): void
    {
        $bag = new InputBag();
        $bag->set('login_form', ['email' => 'email']);
        $this->request->request = $bag;

        $this->expectException(PasswordRequiredException::class);
        $this->expectExceptionMessage('exception.passwordRequired');
        $this->expectExceptionCode(422);

        $this->request
            ->expects($this->never())
            ->method('getSession')
            ->willReturn($this->session);
        $this->session
            ->expects($this->never())
            ->method('set')
            ->with('_security.last_username', null);

        $this->accountAuthenticator->authenticate($this->request);
    }

    public function testCreatePassportWhenEmailAndUserFound(): void
    {
        $bag = new InputBag();
        $bag->set('login_form', ['email' => 'email', 'password' => 'password']);
        $bag->set('_csrf_token', 123);
        $this->request->request = $bag;

        $this->request
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);
        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('_security.last_username', 'email');

        $passport = $this->accountAuthenticator->authenticate($this->request);

        $this->assertSame('email', $passport->getBadge(UserBadge::class)->getUserIdentifier());
        $this->assertSame('123', $passport->getBadge(CsrfTokenBadge::class)->getCsrfToken());
        $this->assertSame('password', $passport->getBadge(PasswordCredentials::class)->getPassword());
    }

    public function testRedirectToLoginUrlWhenIsNotVerifiedOnSuccess(): void
    {
        $this->accountAuthenticatorService
            ->expects($this->once())
            ->method('isVerified')
            ->with($this->request)
            ->willReturn(false);
        $this->accountAuthenticatorService
            ->expects($this->once())
            ->method('getLoginUrl')
            ->willReturn('/login');
        $this->request
            ->expects($this->never())
            ->method('getSession');
        $this->accountAuthenticatorService
            ->expects($this->never())
            ->method('getPanelDashboardUrl');

        $redirectResponse = $this->accountAuthenticator->onAuthenticationSuccess($this->request, $this->token, 'firewallName');

        $this->assertSame('/login', $redirectResponse->getTargetUrl());
    }

    public function testRedirectToTargetPathWhenIsTargetPathFoundOnSuccess(): void
    {
        $this->accountAuthenticatorService
            ->expects($this->once())
            ->method('isVerified')
            ->with($this->request)
            ->willReturn(true);
        $this->accountAuthenticatorService
            ->expects($this->never())
            ->method('getLoginUrl')
            ->willReturn('/login');
        $this->request
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('_security.firewallName.target_path')
            ->willReturn('targetPath');
        $this->accountAuthenticatorService
            ->expects($this->never())
            ->method('getPanelDashboardUrl');

        $redirectResponse = $this->accountAuthenticator->onAuthenticationSuccess($this->request, $this->token, 'firewallName');

        $this->assertSame('targetPath', $redirectResponse->getTargetUrl());
    }

    public function testRedirectToPanelDashboardWhenTargetPathNotFound(): void
    {
        $this->accountAuthenticatorService
            ->expects($this->once())
            ->method('isVerified')
            ->with($this->request)
            ->willReturn(true);
        $this->accountAuthenticatorService
            ->expects($this->never())
            ->method('getLoginUrl')
            ->willReturn('/login');
        $this->request
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('_security.firewallName.target_path')
            ->willReturn(null);
        $this->accountAuthenticatorService
            ->expects($this->once())
            ->method('getPanelDashboardUrl')
            ->willReturn('dashboard');

        $redirectResponse = $this->accountAuthenticator->onAuthenticationSuccess($this->request, $this->token, 'firewallName');

        $this->assertSame('dashboard', $redirectResponse->getTargetUrl());
    }
}
