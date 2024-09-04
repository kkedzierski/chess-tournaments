<?php

namespace App\Account\Ui\Authentication\ForgotPassword;

use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Application\Password\ResetPasswordService;
use App\Account\Ui\AbstractBaseController;
use App\Account\Ui\Exception\EmailRequiredException;
use App\Kernel\Flasher\FlasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForgotPasswordController extends AbstractBaseController
{
    public function __construct(
        private readonly ResetPasswordService $resetPasswordService,
        private readonly FlasherInterface     $flasher,
    ) {
    }

    #[Route('/dashboard/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $email = $form->get('email')->getData();

                if (null === $email || false === is_string($email)) {
                    throw new EmailRequiredException();
                }

                $this->resetPasswordService->processResetPasswordSendEmail($email);

                $this->flasher->success(
                    'dashboard.authentication.resetPassword.email.sent.description',
                    'dashboard.authentication.resetPassword.email.sent.title'
                );

                return $this->redirectToRoute('app_login');
            } catch (CannotSendEmailException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resetPassword.email.error.cannotSendEmail.title'
                );
            } catch (TokenGeneratingFailedException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resetPassword.email.error.tokenGenerating.title'
                );
            } catch (EmailRequiredException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resetPassword.email.error.emailRequired.title'
                );
            } catch (\Throwable $exception) {
                $this->flasher->error(
                    'dashboard.authentication.resetPassword.email.error.description',
                    'dashboard.authentication.resetPassword.email.error.title'
                );
            }
        }

        return $this->render(
            'dashboard/authentication/resetPassword/forgot-password.html.twig',
            [
                'forgotPasswordForm' => $form,
            ]
        );
    }
}
