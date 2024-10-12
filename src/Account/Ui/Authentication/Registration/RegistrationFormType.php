<?php

declare(strict_types=1);

namespace App\Account\Ui\Authentication\Registration;

use App\Account\Domain\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
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
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'dashboard.authentication.register.fields.email',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'required'    => true,
                'label'       => 'dashboard.authentication.register.fields.agreeTerms',
                'label_html'  => true,
                'mapped'      => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'validation.agreeTerms.isTrue',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
            ->add('agreePrivacyPolicy', CheckboxType::class, [
                'required'    => true,
                'label'       => 'dashboard.authentication.register.fields.agreePrivacyPolicy',
                'label_html'  => true,
                'mapped'      => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'validation.agreeTerms.isTrue',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'required'        => true,
                'invalid_message' => 'validation.password.repeat',
                'first_options'   => ['label' => 'dashboard.authentication.register.fields.password'],
                'second_options'  => ['label' => 'dashboard.authentication.register.fields.repeatPassword'],
                'attr'            => ['autocomplete' => 'new-password'],
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
