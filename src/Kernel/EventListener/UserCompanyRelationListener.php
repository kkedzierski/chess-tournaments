<?php

declare(strict_types=1);

namespace App\Kernel\EventListener;

use App\Account\Domain\User;
use App\Company\Domain\Company;
use App\Kernel\Security\CompanyResourceInterface;
use App\Kernel\Security\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

final class UserCompanyRelationListener implements EventSubscriberInterface
{
    public function checkRelation(BeforeCrudActionEvent $event): void
    {
        $adminContext = $event->getAdminContext();
        if (null === $adminContext) {
            throw new \RuntimeException('Admin context not found.');
        }

        $entity = $adminContext->getEntity()->getInstance();
        /** @var User $user */
        $user = $adminContext->getUser();

        if (null === $user->getId()) {
            throw new AccessDeniedException('User not found.');
        }

        if (($entity instanceof UserInterface) && !$user->getId()->equals($entity->getId())) {
            throw new AccessDeniedException('You don\'t have permission to use this resource.');
        }
        //
        //        $companyUuid = null;
        //        if ($entity instanceof CompanyResourceInterface) {
        //            $companyUuid = $entity->getCompanyId();
        //        }
        //
        //        if ($entity instanceof Company) {
        //            $companyUuid = $entity->getId();
        //        }
        //
        //        $userCompany = $user->getCompany();
        //
        //        if (null === $userCompany || null === $userCompany->getUuid()) {
        //            throw new AccessDeniedException('Company not found.');
        //        }
        //
        //        if (null !== $companyUuid && !$userCompany->getUuid()->equals($companyUuid)) {
        //            throw new AccessDeniedException('You don\'t have permission to use this resource.');
        //        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeCrudActionEvent::class => ['checkRelation'],
        ];
    }
}
