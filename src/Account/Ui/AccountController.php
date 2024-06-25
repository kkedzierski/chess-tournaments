<?php

namespace App\Account\Ui;

use App\Account\Application\UserManagerService;
use App\Kernel\MultiplyRolesExpression;
use App\Kernel\Ui\AbstractBaseCrudController;
use App\Kernel\Ui\Form\Field\VichImageField;
use App\Account\Domain\RoleEnum;
use App\Account\Domain\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new MultiplyRolesExpression(RoleEnum::ADMIN, RoleEnum::SUPER_ADMIN, RoleEnum::MODERATOR))]
class AccountController extends AbstractBaseCrudController
{
    public function __construct(
        private readonly UserManagerService $userManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        /** @phpstan-ignore-next-line  */
        $fa2Action = true === $this->getUser()?->isTotpAuthenticationEnabled() ? $this->createDisable2FaAction() : $this->createEnable2FaAction();

        return parent::configureActions($actions)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->add(Crud::PAGE_EDIT, $fa2Action);
    }

    public function configureFields(string $pageName): iterable
    {
        $user = $this->getUser();

        yield FormField::addFieldset('admin.account.block.baseData')
            ->setCssClass('col-sm-6');
        yield EmailField::new('email')
            ->setRequired(true)
            ->onlyOnForms();
        yield VichImageField::new('avatarFile', 'admin.account.avatar')
            ->setDownloadUri('public/uploads/images/avatars')
            ->setImageUri(null)
            ->setUploadedFileNamePattern(sprintf('%s-[slug]-[timestamp].[extension]', $user?->getId()));
        yield FormField::addFieldset('admin.account.block.changePassword')
            ->setCssClass('col-sm-6');
        yield TextField::new('actualPassword')
            ->setLabel('admin.account.actualPassword')
            ->setFormType(PasswordType::class)
            ->setRequired(false)
            ->onlyOnForms();
        yield TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'admin.account.password'],
                'second_options' => ['label' => 'admin.account.confirmPassword'],
                'mapped' => false,
            ])
            ->setRequired(false)
            ->onlyOnForms()
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.account.edit.page');
    }

    /**
     * todo make unit tests or rebuilt password change, cannot make createEditFormBuilder tests.
     *
     * @infection-ignore-all
     *
     * @codeCoverageIgnore
     */
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->userManager->providePasswordEventListener($formBuilder, $context);
    }

    // TODO 2FA

    private function createEnable2FaAction(): Action
    {
        return Action::NEW('enable2Fa')
            ->setLabel('admin.2fa.enable')
            ->setCssClass('btn btn-success')
            ->setIcon('fa fa-lock')
            ->linkToCrudAction('enable2Fa');
    }

    private function createDisable2FaAction(): Action
    {
        return Action::NEW('disable2da')
            ->setLabel('admin.2fa.disable')
            ->setCssClass('btn btn-danger')
            ->setIcon('fa fa-lock-open')
            ->linkToCrudAction('disable2Fa');
    }

    //    public function enable2Fa(ToastrFactory $flasher): RedirectResponse
    //    {
    //        $flasher
    //            ->positionClass('toast-top-center')
    //            ->addSuccess('admin.2fa.turnOn.success.message', 'admin.2fa.turnOn.enable');
    //
    //        return $this->redirect('/authenticate/2fa/enable');
    //    }
    //
    //    public function disable2Fa(ToastrFactory $flasher): RedirectResponse
    //    {
    //        $flasher
    //            ->positionClass('toast-top-center')
    //            ->addSuccess('admin.2fa.turnOff.success.message', 'admin.2fa.turnOff.disable');
    //
    //        return $this->redirect('/authenticate/2fa/disable');
    //    }
}
