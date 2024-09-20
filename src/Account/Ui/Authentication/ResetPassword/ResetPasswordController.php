<?php

namespace App\Account\Ui\Authentication\ResetPassword;

use App\Account\Application\Exception\ResetPasswordException;
use App\Account\Application\Exception\TokenNotFoundException;
use App\Account\Application\Exception\UserNotFoundException;
use App\Account\Application\Password\ResetPasswordService;
use App\Account\Ui\AbstractBaseController;
use App\Account\Ui\Exception\PasswordRequiredException;
use App\Kernel\Flasher\FlasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractBaseController
{
    public function __construct(
        private readonly ResetPasswordService  $resetPasswordService,
        private readonly FlasherInterface      $flasher,
    ) {
    }

    #[Route('/dashboard/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $token = $request->get('token');
                $email = $request->get('emailValue');
                $password = $form->get('password')->getData();
                if (null === $password || false === is_string($password)) {
                    throw new PasswordRequiredException();
                }

                if (null === $token || false === is_string($token)) {
                    throw new TokenNotFoundException();
                }

                if (null === $email || false === is_string($email)) {
                    throw new UserNotFoundException();
                }

                $this->resetPasswordService->resetPassword($token, $email, $password);
                $this->flasher->success(
                    'dashboard.authentication.resetPassword.success.description',
                    'dashboard.authentication.resetPassword.success.title'
                );

                return $this->redirectToRoute('app_login');
            } catch (PasswordRequiredException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resetPassword.error.passwordRequired.title'
                );
            } catch (ResetPasswordException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resetPassword.error.resetPassword.title'
                );
            } catch (TokenNotFoundException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resetPassword.error.tokenNotFound.title'
                );
            } catch (UserNotFoundException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resetPassword.error.userNotFound.title'
                );
            } catch (\Throwable) {
                $this->flasher->error(
                    'dashboard.authentication.resetPassword.error.description',
                    'dashboard.authentication.resetPassword.error.title'
                );
            }
        }

        return $this->render(
            'dashboard/authentication/resetPassword/reset-password.html.twig',
            [
                'resetPasswordForm' => $form,
            ]
        );
    }
}
