<?php

declare(strict_types=1);

namespace App\Tests\Unit\Kernel\EventSubscriber;

use App\Kernel\EventSubscriber\IdResourceInterface;
use App\Kernel\EventSubscriber\IdSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class IdSubscriberTest extends TestCase
{
    private MockObject&LifecycleEventArgs $args;

    private IdSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->args = $this->createMock(LifecycleEventArgs::class);
        $this->subscriber = new IdSubscriber();
    }

    public function testNoSubscribeWhenNoIdResource(): void
    {
        $this->args->expects($this->once())
            ->method('getObject')
            ->willReturn(new class () {});

        $this->subscriber->prePersist($this->args);
    }

    public function testSetIdWhenIdResourceWithoudId(): void
    {
        $entity = new class () implements IdResourceInterface {
            private ?Uuid $id = null;

            public function getId(): ?Uuid
            {
                return $this->id;
            }

            public function setId(?Uuid $id): self
            {
                $this->id = $id;

                return $this;
            }
        };

        $this->args->expects($this->once())
            ->method('getObject')
            ->willReturn($entity);

        $this->subscriber->prePersist($this->args);

        $this->assertNotNull($entity->getId());
    }
}
