<?php

declare(strict_types=1);

namespace App\Tests\Unit\Kernel\CommandManager;

use App\Kernel\CommandManager\CommandManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandManagerTest extends TestCase
{
    private MockObject&LoggerInterface $logger;

    private CommandManager $commandManager;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->commandManager = new CommandManager($this->logger);
    }

    public function testInitializeWithInputAndOutput(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->assertInstanceOf(CommandManager::class, $this->commandManager->initialize($input, $output));
    }

    public function testThrowExceptionWhenInfoIsCalledWithoutInitialize(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('SymfonyStyle is not initialized. Call initialize() method first.');

        $this->commandManager->info('Info message');
    }

    public function testThrowExceptionWhenErrorIsCalledWithoutInitialize(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('SymfonyStyle is not initialized. Call initialize() method first.');

        $this->commandManager->error('Error message');
    }

    public function testThrowExceptionWhenSuccessIsCalledWithoutInitialize(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('SymfonyStyle is not initialized. Call initialize() method first.');

        $this->commandManager->success('Success message');
    }

    public function testLogInfoWhenIoIsInitialized(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->commandManager->initialize($input, $output);
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Info message');

        $this->commandManager->info('Info message');
    }

    public function testLogErrorWhenIoIsInitialized(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->commandManager->initialize($input, $output);
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Error message');

        $this->commandManager->error('Error message');
    }

    public function testLogSuccessWhenIoIsInitialized(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->commandManager->initialize($input, $output);
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Success message');

        $this->commandManager->success('Success message');
    }

    public function testReturnNewProgressBar(): void
    {
        $output = $this->createMock(OutputInterface::class);

        $this->assertInstanceOf(ProgressBar::class, $this->commandManager->generateProgressBar($output));
    }
}
