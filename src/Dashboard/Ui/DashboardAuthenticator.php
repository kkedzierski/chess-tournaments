<?php

namespace App\Dashboard\Ui;

use App\Dashboard\Application\DashboardAuthenticatorService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class DashboardAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private const LAST_USERNAME = '_security.last_username';

    public function __construct(
        private readonly DashboardAuthenticatorService $panelAuthenticatorService
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $this->getLoginUrl($request) === $request->getPathInfo();
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $request->getSession()->set(self::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge(is_string($email) ? $email : ''),
            new PasswordCredentials(is_string($password) ? $password : ''),
            [
//                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->getPanelDashboardUrl());
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->panelAuthenticatorService->getLoginUrl();
    }

    private function getPanelDashboardUrl(): string
    {
        return $this->panelAuthenticatorService->getPanelDashboardUrl();
    }
}
