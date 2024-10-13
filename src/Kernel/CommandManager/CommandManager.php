<?php

declare(strict_types=1);

namespace App\Kernel\CommandManager;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommandManager implements CommandManagerInterface
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function initialize(InputInterface $input, OutputInterface $output): self
    {
        $this->io = new SymfonyStyle($input, $output);

        return $this;
    }

    public function info(string $info): self
    {
        if (null === $this->io) {
            throw new \LogicException('SymfonyStyle is not initialized. Call initialize() method first.');
        }

        $this->io->info($info);
        $this->logger->info($info);

        return $this;
    }

    public function error(string $error): self
    {
        if (null === $this->io) {
            throw new \LogicException('SymfonyStyle is not initialized. Call initialize() method first.');
        }

        $this->io->error($error);
        $this->logger->error($error);

        return $this;
    }

    public function success(string $success): self
    {
        if (null === $this->io) {
            throw new \LogicException('SymfonyStyle is not initialized. Call initialize() method first.');
        }

        $this->io->success($success);
        $this->logger->info($success);

        return $this;
    }

    public function generateProgressBar(OutputInterface $output): ProgressBar
    {
        return new ProgressBar($output);
    }
}
