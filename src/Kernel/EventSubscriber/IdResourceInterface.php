<?php

declare(strict_types=1);

namespace App\Kernel\EventSubscriber;

use Symfony\Component\Uid\Uuid;

interface IdResourceInterface
{
    public function getId(): ?Uuid;

    public function setId(?Uuid $id): self;
}
