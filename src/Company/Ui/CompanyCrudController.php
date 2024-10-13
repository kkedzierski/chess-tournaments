<?php

declare(strict_types=1);

namespace App\Company\Ui;

use App\Account\Domain\RoleEnum;
use App\Company\Domain\Company;
use App\Company\Domain\CompanyRepositoryInterface;
use App\Kernel\EventSubscriber\AbstractBaseCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class CompanyCrudController extends AbstractBaseCrudController
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companyRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_EDIT, 'dashboard.company.title');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel('dashboard.company.save'))
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel('dashboard.company.save'));
    }

    public function new(AdminContext $context): KeyValueStore|Response
    {
        $companyId = $this->getUser()?->getCompanyId();

        if (null !== $companyId) {
            $company = $this->companyRepository->getCompanyById($companyId);
            if (null !== $company) {
                /** @phpstan-ignore-next-line */
                $editUrl = $this->container->get(AdminUrlGenerator::class)
                    ->setController(__CLASS__)
                    ->setAction('edit')
                    ->setEntityId($companyId)
                    ->generateUrl();

                return new RedirectResponse($editUrl);
            }
        }

        return parent::new($context);
    }

    /**
     * @param object $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Company) {
            $user = $this->getUser();

            if (null !== $user && null === $user->getCompanyId()) {
                if (null === $entityInstance->getId()) {
                    $entityInstance->setId(Uuid::v4());
                }

                $user->setCompanyId($entityInstance->getId());
            }
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('dashboard.panel.mainInformation');
        yield FormField::addFieldset('dashboard.panel.mainInformation')
            ->addCssFiles('build/dashboard-main.css')
            ->setIcon('fa fa-house')
            ->collapsible();
        yield TextField::new('uuid')
            ->setLabel('uuid')
            ->onlyOnForms()
            ->setPermission(RoleEnum::SUPER_ADMIN->value)
            ->setDisabled();
        yield TextField::new('taxIdentificationNumber')
            ->setLabel('dashboard.company.taxIdentificationNumber.title')
            ->setHelp('dashboard.company.taxIdentificationNumber.help')
            ->setRequired(true)
            ->setMaxLength(10)
            ->onlyOnForms();
        yield TextField::new('name')
            ->setLabel('dashboard.company.name.title')
            ->setRequired(true)
            ->onlyOnForms();
        yield TextField::new('province')
            ->setLabel('dashboard.company.province.title')
            ->onlyOnForms();
        yield TextField::new('city')
            ->setLabel('dashboard.company.city.title')
            ->onlyOnForms();
        yield TextField::new('zipCode')
            ->setLabel('dashboard.company.zipCode.title')
            ->onlyOnForms();
        yield TextField::new('street')
            ->setLabel('dashboard.company.street.title')
            ->onlyOnForms();
    }
}
