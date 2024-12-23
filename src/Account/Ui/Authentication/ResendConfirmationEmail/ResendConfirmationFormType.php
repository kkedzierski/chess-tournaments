<?php

declare(strict_types=1);

namespace App\Account\Ui\Authentication\ResendConfirmationEmail;

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
class ResendConfirmationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
        ;
    }
}
