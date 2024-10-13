<?php

declare(strict_types=1);

namespace App\Tests\Unit\Company\Ui;

use App\Account\Domain\RoleEnum;
use App\Account\Domain\User;
use App\Company\Domain\Company;
use App\Company\Domain\CompanyRepositoryInterface;
use App\Company\Ui\CompanyCrudController;
use App\Tests\Unit\ConsecutiveParamsTrait;
use DG\BypassFinals;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Uid\Uuid;

class CompanyCrudControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    private MockObject&CompanyRepositoryInterface $companyRepository;

    private MockObject&Actions $actions;

    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&Crud $crud;

    private MockObject&ContainerInterface $container;

    private MockObject&AdminContext $adminContext;

    private CompanyCrudController $controller;

    protected function setUp(): void
    {
        BypassFinals::enable();
        $this->companyRepository = $this->createMock(CompanyRepositoryInterface::class);
        $this->actions = $this->createMock(Actions::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->crud = $this->createMock(Crud::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->adminContext = $this->createMock(AdminContext::class);

        $this->controller = new CompanyCrudController(
            $this->companyRepository,
        );
        $this->controller->setContainer($this->container);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Company::class, $this->controller::getEntityFqcn());
    }

    public function testConfigureCrud(): void
    {
        $this->crud
            ->expects($this->once())
            ->method('setPageTitle')
            ->with(Crud::PAGE_EDIT, 'dashboard.company.title')
            ->willReturnSelf();

        $this->controller->configureCrud($this->crud);
    }

    public function testConfigureActions(): void
    {
        $this->actions
            ->expects($this->exactly(2))
            ->method('remove')
            ->with(...$this->consecutiveParams(
                [Crud::PAGE_EDIT, Action::SAVE_AND_RETURN],
                [Crud::PAGE_NEW, Action::SAVE_AND_RETURN]
            ))
            ->willReturnSelf();
        $this->actions
            ->expects($this->exactly(2))
            ->method('update')
            ->with(...$this->consecutiveParams(
                [Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE],
                [Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER]
            ))
            ->willReturnSelf();

        $this->controller->configureActions($this->actions);
    }

    private function testGetUser(null|(User&MockObject) $user): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
    }

    private function testGetUserInNewMethod(?User $user): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $token = $this->createMock(TokenInterface::class);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->container
            ->expects($this->exactly(2))
            ->method('has')
            ->with(...$this->consecutiveParams(['security.token_storage'], ['security.authorization_checker']))
            ->willReturn(true);
        $this->container
            ->expects($this->exactly(3))
            ->method('get')
            ->with(...$this->consecutiveParams(['security.token_storage'], ['event_dispatcher'], ['security.authorization_checker']))
            ->willReturnOnConsecutiveCalls($tokenStorage, $eventDispatcher, $authorizationChecker);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch');
        $authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        // Verifying only new method from CrudController, my role is not testing the parent methods, so I stop with parent isGranted method in new parent method.
        $this->expectException(ForbiddenActionException::class);
    }

    public function testNoRedirectToEditOnNewWhenUserNotFound(): void
    {
        $this->testGetUserInNewMethod(null);
        $this->companyRepository
            ->expects($this->never())
            ->method('getCompanyById');

        $this->controller->new($this->adminContext);
    }

    public function testNoRedirectToEditOnNewWhenCompanyIdNotFound(): void
    {
        $user = new User();
        $this->testGetUserInNewMethod($user);
        $this->companyRepository
            ->expects($this->never())
            ->method('getCompanyById');

        $this->controller->new($this->adminContext);
    }

    public function testNoRedirectToEditOnNewWhenCompanyNotFound(): void
    {
        $user = new User();
        $user->setCompanyId($companyId = Uuid::v4());
        $this->testGetUserInNewMethod($user);
        $this->companyRepository
            ->expects($this->once())
            ->method('getCompanyById')
            ->with($companyId)
            ->willReturn(null);

        $this->controller->new($this->adminContext);
    }

    public function testRedirectToEditOnNewWhenCompanyFound(): void
    {
        $user = new User();
        $user->setCompanyId($companyId = Uuid::v4());
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $adminUrlGenerator = $this->createMock(AdminUrlGenerator::class);

        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('security.token_storage')
            ->willReturn(true);
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with(...$this->consecutiveParams(['security.token_storage'], [AdminUrlGenerator::class]))
            ->willReturnOnConsecutiveCalls($tokenStorage, $adminUrlGenerator);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->companyRepository
            ->expects($this->once())
            ->method('getCompanyById')
            ->with($companyId)
            ->willReturn(new Company());
        $adminUrlGenerator
            ->expects($this->once())
            ->method('setController')
            ->with(CompanyCrudController::class)
            ->willReturnSelf();
        $adminUrlGenerator
            ->expects($this->once())
            ->method('setAction')
            ->with('edit')
            ->willReturnSelf();
        $adminUrlGenerator
            ->expects($this->once())
            ->method('setEntityId')
            ->with($companyId)
            ->willReturnSelf();
        $adminUrlGenerator
            ->expects($this->once())
            ->method('generateUrl')
            ->willReturn($editUrl = 'edit-company-url');

        $response = $this->controller->new($this->adminContext);

        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
    }

    public function testNoSetCompanyIdToUserWhenUserNotFoundOnPersistEntity()
    {
        $company = $this->createMock(Company::class);
        $this->testGetUser(null);
        $company
            ->expects($this->never())
            ->method('getId');

        $this->controller->persistEntity($this->entityManager, $company);
    }

    public function testNoSetCompanyIdToUserWhenUserHaveCompanyIdOnPersistEntity()
    {
        $company = $this->createMock(Company::class);
        $user = $this->createMock(User::class);
        $this->testGetUser($user);

        $user
            ->expects($this->once())
            ->method('getCompanyId')
            ->willReturn(Uuid::v4());
        $company
            ->expects($this->never())
            ->method('getId');

        $this->controller->persistEntity($this->entityManager, $company);
    }

    public function testSetCompanyIdToUserWhenUserNotHaveCompanyIdOnPersistEntity()
    {
        $company = $this->createMock(Company::class);
        $user = $this->createMock(User::class);
        $this->testGetUser($user);
        $user
            ->expects($this->once())
            ->method('getCompanyId')
            ->willReturn(null);
        $company
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(null);
        $company
            ->expects($this->once())
            ->method('setId');
        $user
            ->expects($this->once())
            ->method('setCompanyId')
            ->with(null);

        $this->controller->persistEntity($this->entityManager, $company);
    }

    public function testConfigureFields(): void
    {
        /** @var $fields FieldTrait[] */
        $fields = [];
        array_push($fields, ...$this->controller->configureFields('pageName'));

        $mainInformationTabField = $fields[0]->getAsDto();
        $this->assertSame('dashboard.panel.mainInformation', $mainInformationTabField->getLabel());

        $mainInformationField = $fields[1]->getAsDto();
        $this->assertSame('dashboard.panel.mainInformation', $mainInformationField->getLabel());
        $this->assertSame('fa fa-house', $mainInformationField->getCustomOption(FormField::OPTION_ICON));

        $uuidField = $fields[2]->getAsDto();
        $this->assertSame('uuid', $uuidField->getLabel());
        $this->assertFalse($uuidField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($uuidField->isDisplayedOn(Crud::PAGE_DETAIL));
        $this->assertSame(RoleEnum::SUPER_ADMIN->value, $uuidField->getPermission());
        $this->assertTrue($uuidField->getFormTypeOption('disabled'));

        $tinField = $fields[3]->getAsDto();
        $this->assertSame('dashboard.company.taxIdentificationNumber.title', $tinField->getLabel());
        $this->assertSame('dashboard.company.taxIdentificationNumber.help', $tinField->getHelp());
        $this->assertFalse($tinField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($tinField->isDisplayedOn(Crud::PAGE_DETAIL));
        $this->assertSame(10, $tinField->getCustomOption(TextField::OPTION_MAX_LENGTH));
        $this->assertTrue($tinField->getFormTypeOption('required'));

        $nameField = $fields[4]->getAsDto();
        $this->assertSame('dashboard.company.name.title', $nameField->getLabel());
        $this->assertFalse($nameField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($nameField->isDisplayedOn(Crud::PAGE_DETAIL));
        $this->assertTrue($nameField->getFormTypeOption('required'));

        $provinceField = $fields[5]->getAsDto();
        $this->assertSame('dashboard.company.province.title', $provinceField->getLabel());
        $this->assertFalse($provinceField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($provinceField->isDisplayedOn(Crud::PAGE_DETAIL));

        $cityField = $fields[6]->getAsDto();
        $this->assertSame('dashboard.company.city.title', $cityField->getLabel());
        $this->assertFalse($cityField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($cityField->isDisplayedOn(Crud::PAGE_DETAIL));

        $zipCodeField = $fields[7]->getAsDto();
        $this->assertSame('dashboard.company.zipCode.title', $zipCodeField->getLabel());
        $this->assertFalse($zipCodeField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($zipCodeField->isDisplayedOn(Crud::PAGE_DETAIL));

        $streetField = $fields[8]->getAsDto();
        $this->assertSame('dashboard.company.street.title', $streetField->getLabel());
        $this->assertFalse($streetField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($streetField->isDisplayedOn(Crud::PAGE_DETAIL));
    }
}
