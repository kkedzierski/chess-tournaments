<?php

declare(strict_types=1);

namespace App\Account\Ui\Authentication\ResetPassword;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

/**
 * Form types are simple and do not need to be tested.
 *
 * @codeCoverageIgnore
 *
 * @infection-ignore-all
 */
class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'required'        => true,
                'invalid_message' => 'validation.password.repeat',
                'first_options'   => ['label' => 'dashboard.authentication.register.fields.password'],
                'second_options'  => ['label' => 'dashboard.authentication.register.fields.repeatPassword'],
                'constraints'     => [
                    new NotBlank([
                        'message' => 'validation.password.notBlank',
                    ]),
                    new Length([
                        'min'        => 10,
                        'minMessage' => 'validation.password.minLength',
                        'max'        => 191,
                    ]),
                    new PasswordStrength([
                        'minScore' => PasswordStrength::STRENGTH_MEDIUM,
                    ]),
                ],
            ])
        ;
    }
}
