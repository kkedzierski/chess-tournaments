<?php

declare(strict_types=1);

namespace App\Tournament\Ui;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Tournament\Domain\Tournament;

/**
 * @implements  ProviderInterface<Tournament>
 */
class TournamentProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return null;
    }
}
