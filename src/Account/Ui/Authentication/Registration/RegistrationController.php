<?php

declare(strict_types=1);

namespace App\Account\Ui\Authentication\Registration;

use App\Account\Application\CreateUserService;
use App\Account\Application\Exception\TokenNotFoundException;
use App\Account\Ui\Authentication\AccountAuthenticator;
use App\Account\Ui\Exception\EmailRequiredException;
use App\Account\Ui\Exception\PasswordRequiredException;
use App\Kernel\Flasher\FlasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly CreateUserService $createUserService,
        private readonly FlasherInterface  $flasher,
    ) {
    }

    #[Route('/dashboard/register', name: 'app_register')]
    public function register(Request $request): ?Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $email = $form->get('email')->getData();
                $password = $form->get('password')->getData();

                if (null === $email || false === is_string($email)) {
                    throw new EmailRequiredException();
                }

                if (null === $password || false === is_string($password)) {
                    throw new PasswordRequiredException();
                }

                $this->createUserService->createUser(
                    $email,
                    $password
                );
                $this->flasher->success(
                    'dashboard.authentication.register.success.description',
                    'dashboard.authentication.register.success.title'
                );

                return $this->redirectToRoute('app_login');
            } catch (EmailRequiredException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.register.email.error.emailRequired.title'
                );
            } catch (PasswordRequiredException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.register.password.error.passwordRequired.title'
                );
            } catch (\Throwable $exception) {
                $this->flasher->error(
                    'dashboard.authentication.register.error.description',
                    'dashboard.authentication.register.error.title'
                );
            }
        }

        return $this->render('dashboard/authentication/registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/dashboard/register/confirm', name: 'app_register_confirm')]
    public function confirmRegistration(Request $request, Security $security): null|Response
    {
        try {
            $token = $request->get('token');

            if (null === $token || false === is_string($token)) {
                throw new TokenNotFoundException();
            }

            $user = $this->createUserService->verifyByToken($token);

            $this->flasher->success(
                'dashboard.authentication.register.confirm.success.description',
                'dashboard.authentication.register.confirm.success.title'
            );

            return $security->login($user, AccountAuthenticator::class, 'main');
        } catch (TokenNotFoundException $exception) {
            $this->flasher->error(
                $exception->getMessage(),
                'dashboard.authentication.register.confirm.error.tokenNotFound.title'
            );
        } catch (\Throwable) {
            $this->flasher->error(
                'dashboard.authentication.register.confirm.error.description',
                'dashboard.authentication.register.confirm.error.title'
            );

            return $this->redirectToRoute('app_register');
        }

        return $this->redirectToRoute('app_register');
    }
}
