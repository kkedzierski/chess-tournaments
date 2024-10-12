<?php

declare(strict_types=1);

namespace App\Company\Domain;

interface GusApiSearchResultRepositoryInterface
{
    public function save(GusApiSearchResult $gusApiSearchResult): void;

    /**
     * @return GusApiSearchResult[]
     */
    public function findAllCreatedAfter(\DateTimeImmutable $date): array;

    public function remove(GusApiSearchResult $gusApiSearchResult): void;
}
