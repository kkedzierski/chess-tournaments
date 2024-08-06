<?php

namespace App\Kernel;

use Flasher\Prime\FlasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class Flasher
{
    /**
     * @var string[]
     */
    private array $defaultOptions = ['position' => 'top-center'];

    public function __construct(
        private readonly FlasherInterface $flasher,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param string[] $options
     */
    public function success(string $message, ?string $title = null, array $options = []): void
    {
        $this->flasher
            ->options(empty($options) ? $this->defaultOptions : $options)
            ->addSuccess(
                $this->translator->trans($message),
                title: $this->translator->trans($title ?? 'Success')
            );
    }

    /**
     * @param string[] $options
     */
    public function error(string $message, ?string $title = null, array $options = []): void
    {
        $this->flasher
            ->options(empty($options) ? $this->defaultOptions : $options)
            ->addError(
                $this->translator->trans($message),
                title: $this->translator->trans($title ?? 'Error')
            );
    }
}
