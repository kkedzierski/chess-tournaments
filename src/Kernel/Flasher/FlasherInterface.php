<?php

declare(strict_types=1);

namespace App\Kernel\Flasher;

interface FlasherInterface
{
    /**
     * @param string[] $options
     * @param string[] $translateParams
     */
    public function success(string $message, ?string $title = null, array $options = [], array $translateParams = []): void;

    /**
     * @param string[] $options
     * @param string[] $translateParams
     */
    public function error(string $message, ?string $title = null, array $options = [], array $translateParams = []): void;
}
