<?php

namespace App\Account\Application;

use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Application\Exception\CreateNewUserException;
use App\Account\Domain\User;
use App\Account\Domain\UserFactory;
use App\Account\Domain\UserRepositoryInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserManagerService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepositoryInterface $userRepository,
        private readonly TranslatorInterface $translator,
        private readonly UserFactory $userFactory,
        private readonly AccountMailerService $accountMailerService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @infection-ignore-all
     *
     * @codeCoverageIgnore
     */
    public function providePasswordEventListener(
        FormBuilderInterface $formBuilder,
        AdminContext $adminContext,
    ): FormBuilderInterface {
        $user = $adminContext->getEntity()->getInstance();

        return $formBuilder->addEventListener(
            FormEvents::POST_SUBMIT,
            $this->hashPasswordForListener($user)
        );
    }

    /**
     * @infection-ignore-all
     *
     * @codeCoverageIgnore
     */
    private function hashPasswordForListener(User $user): callable
    {
        return function ($event) use ($user) {
            /** @var Form $form */
            $form = $event->getForm();

            if (!$form->isValid()) {
                return;
            }

            $newPassword = $form->get('password')->getData();
            $actualPassword = $form->get('actualPassword')->getData();

            if (null === $newPassword) {
                return;
            }

            if (null === $actualPassword || !$this->checkPasswordMatchActual($user, $actualPassword)) {
                $formError = new FormError($this->translator->trans('admin.account.passwordNotMatch'));
                $form->addError($formError);

                return;
            }

            $this->updatePassword($user, $newPassword);
        };
    }

    public function checkPasswordMatchActual(User $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function updatePassword(User $user, mixed $password): ?string
    {
        $passwordHashed = $this->passwordHasher->hashPassword($user, $password);
        $this->userRepository->upgradePassword($user, $passwordHashed);

        return $passwordHashed;
    }

    /**
     * @throws CreateNewUserException
     */
    public function createNewUser(
        string $email,
        string $password,
        bool $sendConfirmationEmail = true
    ): User {
        try {
            $user = $this->userFactory->create($email, $password);
            $this->userRepository->save($user);

            if ($sendConfirmationEmail) {
                $this->sendRegistrationConfirmationEmail($email);
            }
        } catch (\Throwable $e) {
            $this->logger->error(
                'An error occurred while creating a new user.',
                [
                    'exception' => $e,
                    'email' => $email,
                    'class' => __CLASS__,
                ]
            );

            throw new CreateNewUserException();
        }

        return $user;
    }

    /**
     * @throws RandomException
     * @throws CannotSendEmailException
     */
    private function sendRegistrationConfirmationEmail(string $email): void
    {
        $this->accountMailerService->sendEmailToUser(
            $email,
            'dashboard.authentication.registration.confirmation.title',
            'dashboard/authentication/registration/confirmation-email-template.html.twig',
            [],
            'An error occurred while sending registration confirmation email.'
        );
    }
}
