<?php

namespace App\Kernel\Flasher;

use Flasher\Prime\FlasherInterface as BaseFlasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class Flasher implements FlasherInterface
{
    /**
     * @var string[]
     */
    private array $defaultOptions = ['position' => 'top-center'];

    public function __construct(
        private readonly BaseFlasherInterface $flasher,
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
