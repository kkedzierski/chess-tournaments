<?php

namespace App\Account\Ui\Authentication\ForgotPassword;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form types are simple and do not need to be tested.
 *
 * @codeCoverageIgnore
 *
 * @infection-ignore-all
 */
class ForgotPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
        ;
    }
}
