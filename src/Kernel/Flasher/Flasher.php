<?php

declare(strict_types=1);

namespace App\Kernel\Flasher;

use Flasher\Prime\FlasherInterface as BaseFlasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @codeCoverageIgnore Simple service
 *
 * @infection-ignore-all
 */
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
     * @param string[] $translateParams
     */
    public function success(string $message, ?string $title = null, array $options = [], array $translateParams = []): void
    {
        $this->flasher
            ->options(empty($options) ? $this->defaultOptions : $options)
            ->addSuccess(
                $this->translator->trans($message, $translateParams),
                title: $this->translator->trans($title ?? 'Success')
            );
    }

    /**
     * @param string[] $options
     * @param string[] $translateParams
     */
    public function error(string $message, ?string $title = null, array $options = [], array $translateParams = []): void
    {
        $this->flasher
            ->options(empty($options) ? $this->defaultOptions : $options)
            ->addError(
                $this->translator->trans($message, $translateParams),
                title: $this->translator->trans($title ?? 'Error')
            );
    }
}
