<?php

declare(strict_types=1);

namespace App\Account\Application\Password;

use App\Account\Domain\User;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * todo make unit tests or rebuilt password change, cannot make createEditFormBuilder tests.
 *
 * @infection-ignore-all
 *
 * @codeCoverageIgnore
 */
class DashboardPasswordService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TranslatorInterface         $translator,
        private readonly UpdatePasswordService       $passwordManager,
    ) {
    }

    public function provideChangePasswordEventListener(
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
    private function hashPasswordForListener(
        User $user,
    ): callable {
        return function ($event) use ($user) {
            /** @var Form $form */
            $form = $event->getForm();

            if (!$form->isValid()) {
                return;
            }

            $newPassword = $form->get('password')->getData();
            $actualPassword = $form->get('actualPassword')->getData();

            if (null === $newPassword || false === is_string($newPassword)) {
                return;
            }

            if (null === $actualPassword || false === is_string($actualPassword) || !$this->isPasswordValid($user, $actualPassword)) {
                $formError = new FormError($this->translator->trans('admin.account.passwordNotMatch'));
                $form->addError($formError);

                return;
            }

            $this->passwordManager->updatePassword($user, $newPassword);
        };
    }

    public function isPasswordValid(User $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }
}
