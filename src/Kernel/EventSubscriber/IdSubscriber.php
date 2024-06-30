<?php

declare(strict_types=1);

namespace App\Kernel\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Uid\Uuid;

#[AsDoctrineListener(event: Events::prePersist, priority: 0, connection: 'default')]
final readonly class IdSubscriber
{
    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof IdResourceInterface) {
            return;
        }

        if (null === $entity->getId()) {
            $entity->setId(Uuid::v4());
        }
    }
}
