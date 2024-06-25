<?php

namespace App\Account\Application;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountAuthenticatorService
{
    public const LOGIN_ROUTE = 'app_login';

    public const DASHBOARD_ROUTE = 'dashboard';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator
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
}
