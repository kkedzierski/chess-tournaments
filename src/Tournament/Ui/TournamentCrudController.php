<?php

declare(strict_types=1);

namespace App\Tournament\Ui;

use App\Account\Domain\RoleEnum;
use App\Kernel\EventSubscriber\AbstractBaseCrudController;
use App\Kernel\Form\Field\VichImageField;
use App\Kernel\Security\MultiplyRolesExpression;
use App\Tournament\Domain\Tournament;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new MultiplyRolesExpression(RoleEnum::ADMIN, RoleEnum::SUPER_ADMIN, RoleEnum::MODERATOR))]
class TournamentCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tournament::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'admin.partnerCompany.all')
            ->setPageTitle(
                Crud::PAGE_DETAIL,
                static fn (Tournament $tournament) => sprintf('%s', $tournament->getName())
            )
            ->setPageTitle(Crud::PAGE_NEW, 'admin.partnerCompany.new')
            ->setPageTitle(
                Crud::PAGE_EDIT,
                static fn (Tournament $tournament) => sprintf('%s', $tournament->getName())
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                static fn (Action $action) => $action->setLabel('admin.partnerCompany.add')
            );
    }

    public function configureFields(string $pageName): iterable
    {
        $user = $this->getUser();

        //      MAIN INFO PANEL
        yield FormField::addTab('admin.partnerCompany.panel.mainInformation');
        yield FormField::addFieldset('admin.partnerCompany.panel.mainInformation')
            ->addCssFiles('build/admin.css')
            ->addCssClass('col-md-6 real-estate-form-panel')
            ->setIcon('fa fa-house')
            ->collapsible();
        yield IdField::new('id')
            ->hideOnForm();
        yield ImageField::new('imageName')
            ->setLabel('admin.partnerCompany.mainImage')
            ->setBasePath('uploads/images/partnerCompanies')
            ->hideOnForm();
        yield TextField::new('name')
            ->setLabel('admin.name');
        //      IMAGE PANEL
        yield FormField::addFieldset('admin.partnerCompany.panel.image')
            ->addCssClass('col-md-6 real-estate-form-panel')
            ->setIcon('fa fa-images')
            ->onlyOnForms()
            ->collapsible();
        yield VichImageField::new('imageFile', 'admin.partnerCompany.mainImage')
            ->setDownloadUri('public/uploads/images/partnerCompanies')
            ->setImageUri(null)
            ->setUploadedFileNamePattern(sprintf('%s-[slug]-[timestamp].[extension]', $user?->getId()?->toBase32()))
            ->onlyOnForms();
    }
}
