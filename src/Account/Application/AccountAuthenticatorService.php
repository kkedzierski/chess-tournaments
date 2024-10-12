<?php

declare(strict_types=1);

namespace App\Account\Application;

use App\Account\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountAuthenticatorService
{
    public const LOGIN_ROUTE = 'app_login';

    public const DASHBOARD_ROUTE = 'dashboard';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function getLoginUrl(): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function getPanelDashboardUrl(): string
    {
        return $this->urlGenerator->generate(self::DASHBOARD_ROUTE);
    }

    public function isVerified(Request $request): bool
    {
        $email = $request->request->all('login_form')['email'] ?? '';
        $user = $this->userRepository->getByEmail($email);

        return (bool) $user?->isVerified();
    }
}
