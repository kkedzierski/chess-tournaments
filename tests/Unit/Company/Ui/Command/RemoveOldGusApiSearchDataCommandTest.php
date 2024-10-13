<?php

declare(strict_types=1);

namespace App\Tests\Unit\Company\Ui\Command;

use App\Company\Domain\GusApiSearchResult;
use App\Company\Domain\GusApiSearchResultRepositoryInterface;
use App\Company\Ui\Command\RemoveOldGusApiSearchDataCommand;
use App\Kernel\CommandManager\CommandManagerInterface;
use DG\BypassFinals;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Uuid;

class RemoveOldGusApiSearchDataCommandTest extends TestCase
{
    private MockObject&GusApiSearchResultRepositoryInterface $gusApiSearchResultRepository;

    private MockObject&CommandManagerInterface $commandManager;

    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ProgressBar $progressBar;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        BypassFinals::enable();
        $this->gusApiSearchResultRepository = $this->createMock(GusApiSearchResultRepositoryInterface::class);
        $this->commandManager = $this->createMock(CommandManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->progressBar = $this->createMock(ProgressBar::class);

        $command = new RemoveOldGusApiSearchDataCommand(
            $this->gusApiSearchResultRepository,
            $this->commandManager,
            $this->entityManager
        );

        $this->commandTester = new CommandTester($command);
    }

    private function generate20GusApiResult(): array
    {
        $gusApiSearchResults = [];
        for ($i = 0; $i < 20; $i++) {
            $gusApiSearchResults[] = new GusApiSearchResult(Uuid::v4(), 'tin', null);
        }

        return $gusApiSearchResults;
    }

    public function testSuccessWithCommandPromptWhenRemovingOldGusApiSearchDataSuccess(): void
    {
        $gusApiSearchResults = $this->generate20GusApiResult();
        $this->commandManager
            ->expects($this->once())
            ->method('initialize');
        $this->commandManager
            ->expects($this->once())
            ->method('info')
            ->with('Start removing old GUS API search data.');
        $this->gusApiSearchResultRepository
            ->expects($this->once())
            ->method('findAllCreatedAfter')
            ->with($this->callback(fn (\DateTimeImmutable $dateTime) => $dateTime->format('Y-m-d') === (new \DateTimeImmutable('-3 month'))->format('Y-m-d')))
            ->willReturn($gusApiSearchResults);
        $this->commandManager
            ->expects($this->once())
            ->method('generateProgressBar')
            ->willReturn($this->progressBar);
        $this->progressBar
            ->expects($this->once())
            ->method('start')
            ->with(20);
        $this->progressBar
            ->expects($this->once())
            ->method('iterate')
            ->with($gusApiSearchResults)
            ->willReturnOnConsecutiveCalls(...array_chunk($gusApiSearchResults, 20));
        $this->entityManager
            ->expects($this->exactly(20))
            ->method('remove');
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');

        $this->commandManager->expects($this->once())
            ->method('success')
            ->with('Old GUS API search data has been removed.');

        $this->assertSame(0, $this->commandTester->execute([]));
    }

    public function testFailureWithCommandPromptWhenRemovingOldGusApiSearchDataFails(): void
    {
        $gusApiSearchResults = $this->generate20GusApiResult();
        $this->commandManager
            ->expects($this->once())
            ->method('initialize');
        $this->commandManager
            ->expects($this->once())
            ->method('info')
            ->with('Start removing old GUS API search data.');
        $this->gusApiSearchResultRepository
            ->expects($this->once())
            ->method('findAllCreatedAfter')
            ->with($this->callback(fn (\DateTimeImmutable $dateTime) => $dateTime->format('Y-m-d') === (new \DateTimeImmutable('-3 month'))->format('Y-m-d')))
            ->willReturn($gusApiSearchResults);
        $this->commandManager
            ->expects($this->once())
            ->method('generateProgressBar')
            ->willReturn($this->progressBar);
        $this->progressBar
            ->expects($this->once())
            ->method('start')
            ->with(20);
        $this->progressBar
            ->expects($this->once())
            ->method('iterate')
            ->with($gusApiSearchResults)
            ->willReturnOnConsecutiveCalls(...array_chunk($gusApiSearchResults, 20));
        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->willThrowException(new \Exception('An error occurred'));
        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $this->commandManager->expects($this->once())
            ->method('error')
            ->with('Error on removing old GUS API search data [An error occurred]');

        $this->assertSame(1, $this->commandTester->execute([]));
    }
}
