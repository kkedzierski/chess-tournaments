<?php

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\AccountAuthenticatorService;
use App\Account\Domain\User;
use App\Account\Domain\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountAuthenticatorServiceTest extends TestCase
{
    private MockObject&UrlGeneratorInterface $urlGenerator;
    private MockObject&UserRepositoryInterface $userRepository;

    private AccountAuthenticatorService $service;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->service = new AccountAuthenticatorService($this->urlGenerator, $this->userRepository);
    }

    public function testGenerateLoginUrl(): void
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(AccountAuthenticatorService::LOGIN_ROUTE)
            ->willReturn('/login');

        $this->assertSame('/login', $this->service->getLoginUrl());
    }

    public function testGenerateDashboardUrl(): void
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(AccountAuthenticatorService::DASHBOARD_ROUTE)
            ->willReturn('/dashboard');

        $this->assertSame('/dashboard', $this->service->getPanelDashboardUrl());
    }

    public function testReturnIsVerifiedAsBoolWhenUserFound(): void
    {
        $user = new User();
        $user->verify();
        $bag = new InputBag();
        $bag->set('login_form', ['email' => 'email']);
        $request = $this->createMock(Request::class);
        $request->request = $bag;

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('email')
            ->willReturn($user);

        $this->assertTrue($this->service->isVerified($request));
    }

    public function testReturnFalseWhenUserNotFoundFound(): void
    {
        $bag = new InputBag();
        $bag->set('login_form', ['email' => null]);
        $request = $this->createMock(Request::class);
        $request->request = $bag;

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with(null)
            ->willReturn(null);

        $this->assertFalse($this->service->isVerified($request));
    }

}
