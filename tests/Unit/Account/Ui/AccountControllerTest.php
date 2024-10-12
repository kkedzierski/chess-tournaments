<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Ui;

use App\Account\Application\Password\DashboardPasswordService;
use App\Account\Domain\User;
use App\Account\Ui\AccountCrudController;
use App\Kernel\Flasher\FlasherInterface;
use DG\BypassFinals;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AccountControllerTest extends TestCase
{
    private MockObject&DashboardPasswordService $dashboardPasswordService;

    private MockObject&Actions $actions;

    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&Crud $crud;

    private MockObject&ContainerInterface $container;

    private MockObject&FlasherInterface $flasher;

    private AccountCrudController $controller;

    protected function setUp(): void
    {
        BypassFinals::enable();
        $this->dashboardPasswordService = $this->createMock(DashboardPasswordService::class);
        $this->actions = $this->createMock(Actions::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->crud = $this->createMock(Crud::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->flasher = $this->createMock(FlasherInterface::class);

        $this->controller = new AccountCrudController(
            $this->dashboardPasswordService,
        );
        $this->controller->setContainer($this->container);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(User::class, $this->controller::getEntityFqcn());
    }

    private static function createEnable2FaAction(): Action
    {
        return Action::NEW('enable2Fa')
            ->setLabel('admin.2fa.enable')
            ->setCssClass('btn btn-success')
            ->setIcon('fa fa-lock')
            ->linkToCrudAction('enable2Fa');
    }

    private static function createDisable2FaAction(): Action
    {
        return Action::NEW('disable2da')
            ->setLabel('admin.2fa.disable')
            ->setCssClass('btn btn-danger')
            ->setIcon('fa fa-lock-open')
            ->linkToCrudAction('disable2Fa');
    }

    /**
     * @return array<int, array<int, false|Action>>
     */
    public static function providerFor2FaAction(): array
    {
        return [
            [false, self::createEnable2FaAction()],
            [true, self::createDisable2FaAction()],
        ];
    }

    #[DataProvider('providerFor2FaAction')]
    public function testConfigureActions(
        bool   $isTotpAuthenticationEnabled,
        Action $fa2Action,
    ): void {
        $user = $this->createMock(User::class);
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
        $user
            ->expects($this->once())
            ->method('isTotpAuthenticationEnabled')
            ->willReturn($isTotpAuthenticationEnabled);
        $this->actions
            ->expects($this->once())
            ->method('remove')
            ->with(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->willReturnSelf();
        $this->actions
            ->expects($this->once())
            ->method('add')
            ->with(Crud::PAGE_EDIT, $fa2Action)
            ->willReturnSelf();

        $this->controller->configureActions($this->actions);
    }

    public function testUpdateEntity(): void
    {
        $admin = new User();

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($admin);
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->controller->updateEntity($this->entityManager, $admin);
    }

    public function testConfigureFields(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $admin = $this->createMock(User::class);

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
            ->willReturn($admin);

        /** @var $fields FieldTrait[] */
        $fields = [];
        array_push($fields, ...$this->controller->configureFields('pageName'));

        $baseDataBlockField = $fields[0]->getAsDto();
        $this->assertSame('admin.account.block.baseData', $baseDataBlockField->getLabel());
        $this->assertSame('col-sm-6', $baseDataBlockField->getCssClass());

        $emailField = $fields[1]->getAsDto();
        $this->assertFalse($emailField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($emailField->isDisplayedOn(Crud::PAGE_DETAIL));
        $this->assertTrue($emailField->getFormTypeOption('required'));

        $imageField = $fields[2]->getAsDto();
        $this->assertSame('admin.account.avatar', $imageField->getLabel());
        $this->assertSame('public/uploads/images/avatars', $imageField->getCustomOption('download_uri'));
        $this->assertSame('-[slug]-[timestamp].[extension]', $imageField->getCustomOption('uploadedFileNamePattern'));

        $changePasswordBlockField = $fields[3]->getAsDto();
        $this->assertSame('admin.account.block.changePassword', $changePasswordBlockField->getLabel());
        $this->assertSame('col-sm-6', $changePasswordBlockField->getCssClass());

        $actualPasswordField = $fields[4]->getAsDto();
        $this->assertSame('admin.account.actualPassword', $actualPasswordField->getLabel());
        $this->assertSame(PasswordType::class, $actualPasswordField->getFormType());
        $this->assertFalse($actualPasswordField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($actualPasswordField->isDisplayedOn(Crud::PAGE_DETAIL));
        $this->assertFalse($actualPasswordField->getFormTypeOption('required'));

        $passwordField = $fields[5]->getAsDto();
        $this->assertSame(RepeatedType::class, $passwordField->getFormType());
        $this->assertFalse($passwordField->isDisplayedOn(Crud::PAGE_INDEX));
        $this->assertFalse($passwordField->isDisplayedOn(Crud::PAGE_DETAIL));
        $this->assertSame(PasswordType::class, $passwordField->getFormTypeOption('type'));
        $this->assertSame(['label' => 'admin.account.password'], $passwordField->getFormTypeOption('first_options'));
        $this->assertSame(['label' => 'admin.account.confirmPassword'], $passwordField->getFormTypeOption('second_options'));
        $this->assertFalse($passwordField->getFormTypeOption('mapped'));
        $this->assertFalse($passwordField->getFormTypeOption('required'));
        $this->assertFalse($passwordField->getFormTypeOption('required'));
    }

    public function testConfigureCrud(): void
    {
        $this->crud
            ->expects($this->once())
            ->method('setPageTitle')
            ->with(Crud::PAGE_EDIT, 'admin.account.edit.page')
            ->willReturnSelf();

        $this->controller->configureCrud($this->crud);
    }

    public function testEnable2Fa(): void
    {
        $this->assertEquals(new RedirectResponse('/authenticate/2fa/enable', 302), $this->controller->enable2Fa($this->flasher));
    }

    public function testDisable2Fa(): void
    {
        $this->assertEquals(new RedirectResponse('/authenticate/2fa/disable', 302), $this->controller->disable2Fa($this->flasher));
    }
}
