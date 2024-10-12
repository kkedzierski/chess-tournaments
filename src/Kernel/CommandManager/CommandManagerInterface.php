<?php

declare(strict_types=1);

namespace App\Kernel\CommandManager;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface CommandManagerInterface
{
    public function initialize(InputInterface $input, OutputInterface $output): self;

    public function info(string $info): self;

    public function error(string $error): self;

    public function success(string $success): self;

    public function generateProgressBar(OutputInterface $output): ProgressBar;
}
