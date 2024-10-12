<?php

declare(strict_types=1);

namespace App\Company\Ui\Command;

use App\Company\Domain\GusApiSearchResultRepositoryInterface;
use App\Kernel\CommandManager\CommandManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'chess:remove:old:gusApi',
    description: 'Remove old GUS API search data.',
    aliases: ['chess:r:o:ga'],
)]
class RemoveOldGusApiSearchDataCommand extends Command
{
    public function __construct(
        private readonly GusApiSearchResultRepositoryInterface $gusApiSearchResultRepository,
        private readonly CommandManagerInterface               $commandManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->commandManager->initialize($input, $output);
        $this->commandManager->info('Start removing old GUS API search data.');

        $gusData = $this->gusApiSearchResultRepository->findAllCreatedAfter(new \DateTimeImmutable('-3 month'));

        $progressBar = $this->commandManager->generateProgressBar($output);
        $progressBar->start(count($gusData));

        try {
            $i = 0;
            foreach ($progressBar->iterate($gusData) as $gusApiSearchResult) {
                $this->entityManager->remove($gusApiSearchResult);
                if (0 === ++$i % 20) {
                    $this->entityManager->flush();
                }
            }
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $this->commandManager->error(sprintf('Error on removing old GUS API search data [%s]', $exception->getMessage()));

            return Command::FAILURE;
        }

        $this->commandManager->success('Old GUS API search data has been removed.');

        return Command::SUCCESS;
    }
}
