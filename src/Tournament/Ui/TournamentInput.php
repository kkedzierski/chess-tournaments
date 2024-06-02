<?php

namespace App\Tournament\Ui;

class TournamentInput
{
    private string $name;
    private string $type;
    private string $pace;
    private string $status;
    private string $startDate;
    private string $endDate;

    private string $source;

    public function __construct(
        string $name,
        string $type,
        string $pace,
        string $status,
        string $startDate,
        string $endDate,
        string $source,
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->pace = $pace;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->source = $source;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPace(): string
    {
        return $this->pace;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
