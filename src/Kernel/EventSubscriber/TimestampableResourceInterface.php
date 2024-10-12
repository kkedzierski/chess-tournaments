<?php

declare(strict_types=1);

namespace App\Kernel\EventSubscriber;

interface TimestampableResourceInterface
{
    public function getCreatedAt(): ?\DateTimeImmutable;

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self;

    public function getUpdatedAt(): ?\DateTimeImmutable;

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self;

    public function setCreatedBy(?string $createdBy): self;

    public function getCreatedBy(): ?string;

    public function setUpdatedBy(?string $updatedBy): self;

    public function getUpdatedBy(): ?string;

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self;

    public function getDeletedAt(): ?\DateTimeImmutable;

    public function setDeletedBy(?string $deletedBy): self;

    public function getDeletedBy(): ?string;
}
