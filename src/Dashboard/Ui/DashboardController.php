<?php

namespace App\Dashboard\Ui;

use App\Kernel\Infrastructure\YamlParser;
use App\Kernel\MultiplyRolesExpression;
use App\Kernel\Ui\UserInterface;
use App\Account\Domain\RoleEnum;
use App\Account\Domain\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @method UserInterface getUser()
 */
#[IsGranted(new MultiplyRolesExpression(RoleEnum::ADMIN, RoleEnum::SUPER_ADMIN, RoleEnum::MODERATOR))]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly YamlParser $yamlParser,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        $dashboardData = $this->getDashboardData();

        return $this->render('dashboard/main/index.html.twig', [
            'dashboardData' => $dashboardData,
        ]);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('dashboard-main')
            ->addCssFile('build/dashboard-main.css');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('panel.dashboard.title')
            ->setFaviconPath('/images/favicon.ico')
            ->disableDarkMode(false);
        //            ->setLocales([
        //                'pl' => '🇵🇱 Polski',
        //                'en' => '🇬🇧 English',
        //                'de' => '🇩🇪 Deutsch',
        //            ]);
        //        https://emojiterra.com/
    }

    /**
     * @param User $user
     *
     * @infection-ignore-all
     */
    public function configureUserMenu($user): UserMenu
    {
        if ($this->authorizationChecker->isGranted(RoleEnum::SUPER_ADMIN->value)) {
            return parent::configureUserMenu($user)
                ->setAvatarUrl($user->getAvatarUrl())
                ->addMenuItems([
//                    MenuItem::linkToCrud('admin.account.edit.page', 'fa fa-user-cog', Account::class)
//                        ->setController(AccountController::class)
//                        ->setAction(Action::EDIT)
//                        ->setEntityId($user->getId() ?: 0),
//                    MenuItem::linkToCrud('admin.company.edit.page', 'fa fa-gear', Company::class)
//                        ->setController(CompanyController::class)
//                        ->setAction(Action::EDIT)
//                        ->setEntityId($company->getId() ?: 0),
//                    MenuItem::linkToCrud('admin.errorLog.index.page', 'fa fa-circle-exclamation', ErrorLog::class)
//                        ->setController(ErrorLogController::class)
//                        ->setAction(Action::INDEX),
                ]);
        }
        if ($this->authorizationChecker->isGranted(RoleEnum::ADMIN->value)) {
            return parent::configureUserMenu($user)
                ->setAvatarUrl($user->getAvatarUrl())
                ->addMenuItems([
//                    MenuItem::linkToCrud('admin.account.edit.page', 'fa fa-user-cog', Account::class)
//                        ->setController(AccountController::class)
//                        ->setAction(Action::EDIT)
//                        ->setEntityId($user->getId() ?: 0),
//                    MenuItem::linkToCrud('admin.company.edit.page', 'fa fa-gear', Company::class)
//                        ->setController(CompanyController::class)
//                        ->setAction(Action::EDIT)
//                        ->setEntityId($company->getId() ?: 0),
                ]);
        }

        return parent::configureUserMenu($user)
            ->setAvatarUrl($user->getAvatarUrl())
            ->addMenuItems([
//                MenuItem::linkToCrud('admin.account.edit.page', 'fa fa-user-cog', Account::class)
//                    ->setController(AccountController::class)
//                    ->setAction(Action::EDIT)
//                    ->setEntityId($user->getId() ?: 0),
            ]);
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_DETAIL,
                Action::EDIT,
                static fn (Action $action) => $action->setIcon('fa fa-edit')
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::INDEX,
                static fn (Action $action) => $action->setIcon('fa fa-list')
            );
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('panel.dashboard.menuItems.title', 'fa fa-home');
        yield MenuItem::section('admin.sections');
        //        yield MenuItem::linkToCrud('admin.realEstate.all', 'fa fa-building-user', RealEstate::class)
        //            ->setController(RealEstateController::class);
        //        yield MenuItem::linkToCrud('admin.newsPost.all', 'fa fa-regular fa-newspaper', NewsPost::class);
        //        yield MenuItem::linkToCrud('admin.team.all', 'fa fa-people-group', Team::class);
        //        yield MenuItem::linkToCrud('admin.partnerCompany.all', 'fa fa-sitemap', PartnerCompany::class);
        //        if ($this->authorizationChecker->isGranted(RoleEnum::SUPER_ADMIN->value)) {
        //            yield MenuItem::section('admin.advancedSettings');
        //            yield MenuItem::linkToCrud('admin.user.all', 'fa fa-users', Account::class)
        //                ->setController(UserController::class);
    }


    private function getDashboardData(): mixed
    {
        $filePath = sprintf('%s/./Assets/menu-dashboard-items.yaml', __DIR__);

        $dashboardData = $this->yamlParser->getDataFromFile($filePath);

        /** @phpstan-ignore-next-line */
        foreach ($dashboardData as $menuItem => $data) {
            /** @phpstan-ignore-next-line */
            $dashboardData[$menuItem]['url'] = $this->adminUrlGenerator
                /** @phpstan-ignore-next-line */
                ->setController($data['controller'])
                ->setAction(Action::INDEX)
                ->generateUrl();
        }

        return $dashboardData;
    }
}
