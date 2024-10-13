<?php

declare(strict_types=1);

namespace App\Tests\Unit\Kernel\Mock\Translator;

use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorMock implements TranslatorInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly array $messages = [],
    ) {
    }

    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    public function getCatalogue(string $locale = null): CatalogueMock
    {
        return new CatalogueMock($this->messages);
    }

    public function setLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }
}
