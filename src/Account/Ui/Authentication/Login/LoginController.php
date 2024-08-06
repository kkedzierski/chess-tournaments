<?php

namespace App\Account\Ui\Authentication\Login;

use App\Account\Application\AccountAuthenticatorService;
use App\Account\Ui\AbstractBaseController;
use App\Kernel\Flasher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractBaseController
{
    #[Route(path: '/dashboard/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Flasher $flasher): Response
    {
        $user = $this->getUser();

        if ($user && false === $user->isVerified()) {
            $flasher->error('dashboard.authentication.login.verifyEmail');
        }

        if ($user && $user->isAdmin() && $user->isVerified()) {
            return $this->redirectToRoute(AccountAuthenticatorService::DASHBOARD_ROUTE);
        }

        // get the authentication error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class);

        return $this->render(
            'dashboard/authentication/login/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
                'loginForm' => $form->createView(),
            ]
        );
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
