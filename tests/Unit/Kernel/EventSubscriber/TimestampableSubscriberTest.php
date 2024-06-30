<?php

namespace App\Tests\Unit\Kernel\EventSubscriber;

use App\Kernel\EventSubscriber\TimestampableResourceInterface;
use App\Kernel\EventSubscriber\TimestampableSubscriber;
use App\Account\Domain\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TimestampableSubscriberTest extends TestCase
{
    private MockObject&TimestampableResourceInterface $entity;
    private MockObject&LifecycleEventArgs $event;
    private MockObject&Security $security;
    private MockObject&LoggerInterface $logger;
    private MockObject&User $user;

    private TimestampableSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->entity = $this->createMock(TimestampableResourceInterface::class);
        $this->event = $this->createMock(LifecycleEventArgs::class);
        $this->security = $this->createMock(Security::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->user = $this->createMock(User::class);

        $this->subscriber = new TimestampableSubscriber($this->security, $this->logger);
    }

    public function testPrePersistWrongEntity(): void
    {
        $entity = new class () {
            private ?\DateTimeImmutable $createdAt = null;

            public function getCreatedAt(): ?\DateTimeImmutable
            {
                return $this->createdAt;
            }
        };

        $this->event->expects($this->once())->method('getObject')->willReturn($entity);
        $this->subscriber->prePersist($this->event);

        $this->assertNull($entity->getCreatedAt());
    }

    public function testPrePersistTimestampableFields(): void
    {
        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        $this->event->expects($this->once())->method('getObject')->willReturn($this->entity);
        $this->entity
            ->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(null);
        $this->entity
            ->expects($this->once())
            ->method('setCreatedAt')
            ->with($dateTime)
            ->willReturnSelf();
        $this->entity
            ->expects($this->once())
            ->method('setUpdatedAt')
            ->with($dateTime)
            ->willReturnSelf();

        $this->subscriber->prePersist($this->event);
    }

    public function testLogErrorWhenSettingUserFailsOnPrePersist(): void
    {
        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        $this->event->expects($this->once())->method('getObject')->willReturn($this->entity);
        $this->entity
            ->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(null);
        $this->entity
            ->expects($this->once())
            ->method('setCreatedAt')
            ->with($dateTime)
            ->willReturnSelf();
        $this->entity
            ->expects($this->once())
            ->method('setUpdatedAt')
            ->with($dateTime)
            ->willReturnSelf();
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);
        $this->user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn('user-identifier');
        $this->entity
            ->expects($this->once())
            ->method('setCreatedBy')
            ->with('user-identifier')
            ->willThrowException($exception = new \Exception());
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Setting user data for resource failed.',
                [
                    'exception' => $exception,
                    'class' => TimestampableSubscriber::class,
                ]
            );

        $this->subscriber->prePersist($this->event);
    }

    public function testLogErrorWhenSettingUserFailsOnPreUpdate(): void
    {
        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        $this->event->expects($this->once())->method('getObject')->willReturn($this->entity);
        $this->entity
            ->expects($this->once())
            ->method('setUpdatedAt')
            ->with($dateTime)
            ->willReturnSelf();
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);
        $this->user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn('user-identifier');
        $this->entity
            ->expects($this->once())
            ->method('setUpdatedBy')
            ->with('user-identifier')
            ->willThrowException($exception = new \Exception());
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Setting user data for resource failed.',
                [
                    'exception' => $exception,
                    'class' => TimestampableSubscriber::class,
                ]
            );

        $this->subscriber->preUpdate($this->event);
    }

    public function testLogErrorWhenSettingUserFailsOnPreRemove(): void
    {
        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        $this->event->expects($this->once())->method('getObject')->willReturn($this->entity);
        $this->entity
            ->expects($this->once())
            ->method('setDeletedAt')
            ->with($dateTime)
            ->willReturnSelf();
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);
        $this->user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn('user-identifier');
        $this->entity
            ->expects($this->once())
            ->method('setDeletedBy')
            ->with('user-identifier')
            ->willThrowException($exception = new \Exception());
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Setting user data for resource failed.',
                [
                    'exception' => $exception,
                    'class' => TimestampableSubscriber::class,
                ]
            );

        $this->subscriber->preRemove($this->event);
    }
}
