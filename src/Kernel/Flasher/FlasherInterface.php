<?php

namespace App\Kernel\Flasher;

interface FlasherInterface
{
    /**
     * @param string[] $options
     */
    public function success(string $message, ?string $title = null, array $options = []): void;
    /**
     * @param string[] $options
     */
    public function error(string $message, ?string $title = null, array $options = []): void;
}
