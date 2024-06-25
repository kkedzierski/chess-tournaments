<?php

namespace App\Account\Ui\Authentication\Registration;

use App\Account\Application\PasswordTokenService;
use App\Account\Application\UserManagerService;
use App\Account\Ui\Authentication\AccountAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly UserManagerService               $userManager,
        private readonly PasswordTokenService $passwordTokenService,
    ) {
    }

    #[Route('/dashboard/register', name: 'app_register')]
    public function register(
        Request $request,
        Security $security,
    ): ?Response {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $this->userManager->createNewUser(
                    $form->get('email')->getData(),
                    $form->get('password')->getData()
                );
            } catch (\Throwable $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_register');
            }

            $this->addFlash('info', 'dashboard.authentication.register.verificationEmailSent');
            return $security->login($user, AccountAuthenticator::class, 'main');
        }

        return $this->render('dashboard/authentication/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/dashboard/register/confirm/{token}', name: 'app_register_confirm')]
    public function confirmRegistration(Request $request, Security $security): Response
    {
        try {
            $token = $request->get('token');
            $passwordToken = $this->passwordTokenService->setAsVerified($token);
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_register');
        }

        return $security->login($passwordToken->getUser(), AccountAuthenticator::class, 'main');
    }
}
