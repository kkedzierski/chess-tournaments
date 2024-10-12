<?php

declare(strict_types=1);

namespace App\Account\Ui\Authentication\Login;

use App\Account\Application\AccountAuthenticatorService;
use App\Account\Ui\AbstractBaseController;
use App\Kernel\Flasher\FlasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractBaseController
{
    #[Route(path: '/dashboard/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, FlasherInterface $flasher): Response
    {
        $user = $this->getUser();
        $error = $authenticationUtils->getLastAuthenticationError();

        if (null === $error && $user && false === $user->isVerified()) {
            $resendUrl = $this->generateUrl('app_register_resend_confirmation_email');

            $flasher->error('dashboard.authentication.login.verifyEmail', translateParams: ['%resend_url%' => $resendUrl]);
        }

        if ($user && $user->isAdmin() && $user->isVerified()) {
            return $this->redirectToRoute(AccountAuthenticatorService::DASHBOARD_ROUTE);
        }

        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class);

        return $this->render(
            'dashboard/authentication/login/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error'         => $error,
                'loginForm'     => $form->createView(),
            ]
        );
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
