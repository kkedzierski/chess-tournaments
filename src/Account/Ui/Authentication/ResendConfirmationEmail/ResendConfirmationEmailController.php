<?php

declare(strict_types=1);

namespace App\Account\Ui\Authentication\ResendConfirmationEmail;

use App\Account\Application\CreateUserService;
use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Ui\AbstractBaseController;
use App\Account\Ui\Exception\EmailRequiredException;
use App\Kernel\Flasher\FlasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ResendConfirmationEmailController extends AbstractBaseController
{
    public function __construct(
        private readonly CreateUserService $createUserService,
        private readonly FlasherInterface           $flasher,
    ) {
    }

    #[Route('/dashboard/register/resend-confirmation-email', name: 'app_register_resend_confirmation_email')]
    public function resendConfirmationEmail(Request $request): Response
    {
        $form = $this->createForm(ResendConfirmationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $email = $form->get('email')->getData();

                if (null === $email || false === is_string($email)) {
                    throw new EmailRequiredException();
                }
                $this->createUserService->resendConfirmationEmail($email);

                $this->flasher->success(
                    'dashboard.authentication.resendConfirmation.success.description',
                    'dashboard.authentication.resendConfirmation.success.title'
                );

                return $this->redirectToRoute('app_login');
            } catch (EmailRequiredException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resendConfirmation.error.emailRequired.title'
                );
            } catch (TokenGeneratingFailedException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resendConfirmation.error.tokenNotFound.title'
                );
            } catch (CannotSendEmailException $exception) {
                $this->flasher->error(
                    $exception->getMessage(),
                    'dashboard.authentication.resendConfirmation.error.cannotSendEmail.title'
                );
            } catch (\Throwable) {
                $this->flasher->error(
                    'dashboard.authentication.register.resendConfirmation.error.description',
                    'dashboard.authentication.register.resendConfirmation.error.title'
                );
            }
        }

        return $this->render('dashboard/authentication/registration/resend-confirmation-email.twig', [
            'resendConfirmationForm' => $form,
        ]);
    }
}
