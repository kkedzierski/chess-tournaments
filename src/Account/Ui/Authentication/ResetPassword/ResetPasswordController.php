<?php

namespace App\Account\Ui\Authentication\ResetPassword;

use App\Account\Application\ResetPasswordService;
use App\Account\Ui\AbstractBaseController;
use App\Account\Ui\Exception\EmailRequiredException;
use App\Account\Ui\Exception\PasswordRequiredException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordController extends AbstractBaseController
{
    public function __construct(
        private readonly ResetPasswordService $resetPasswordService,
        private readonly TranslatorInterface $translator,
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

                if (null === $email) {
                    throw new EmailRequiredException();
                }

                $this->resetPasswordService->sendResetPasswordEmail($email);

                $this->addFlash('success', $this->translator->trans(
                    'dashboard.authentication.resetPassword.email.sent'
                ));
                $this->redirectToRoute('app_login');
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render(
            'dashboard/authentication/resetPassword/forgot-password.html.twig',
            [
                'forgotPasswordForm' => $form,
            ]
        );
    }

    #[Route('/dashboard/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $token = $request->get('token');
                $email = $request->get('email');
                $password = $form->get('password')->getData();

                if (null === $password) {
                    throw new PasswordRequiredException();
                }

                $this->resetPasswordService->resetPassword($token, $email, $form->get('password')->getData());

                $this->addFlash('success', $this->translator->trans(
                    'dashboard.authentication.resetPassword.success'
                ));
                $this->redirectToRoute('app_login');
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
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
