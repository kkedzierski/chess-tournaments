<?php

declare(strict_types=1);

namespace App\Tests\Unit\Kernel\Translator;

use App\Kernel\Translator\TranslatorUXController;
use App\Tests\Unit\Kernel\Mock\Translator\CatalogueMock;
use App\Tests\Unit\Kernel\Mock\Translator\TranslatorMock;
use App\Tests\Unit\Stub\CacheStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TranslatorUXControllerTest extends TestCase
{
    private MockObject&CacheInterface $cache;

    private MockObject&ItemInterface $item;

    private CacheStub $cacheStub;

    private MockObject&TranslatorMock $translator;

    private TranslatorUXController $controller;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->item = $this->createMock(ItemInterface::class);
        $this->translator = $this->createMock(TranslatorMock::class);

        $this->cacheStub = new CacheStub($this->cache);

        $this->controller = new TranslatorUXController($this->translator, $this->cacheStub);
    }

    public function testReturnAllTranslationsWhenAllProvided(): void
    {
        $this->cacheStub->setItem($this->item);
        $this->cacheStub
            ->get('translations_all', function (): array {
                $catalogue = new CatalogueMock(['messages' => ['foo' => 'bar']]);
                $this->item
                    ->expects($this->once())
                    ->method('expiresAfter')
                    ->with(3600)
                    ->willReturnSelf();
                $this->translator
                    ->expects($this->once())
                    ->method('getCatalogue')
                    ->willReturn($catalogue);

                return $catalogue->all();
            });

        $this->controller->trans('all');
    }

    public function testOnlyKeyTranslatedValue(): void
    {
        $this->cacheStub->setItem($this->item);
        $this->cacheStub
            ->get('translations_all', function (): string {
                $catalogue = new CatalogueMock(['messages' => ['foo' => 'bar']]);
                $this->item
                    ->expects($this->once())
                    ->method('expiresAfter')
                    ->with(3600)
                    ->willReturnSelf();
                $this->translator
                    ->expects($this->never())
                    ->method('getCatalogue')
                    ->willReturn($catalogue);
                $this->translator
                    ->expects($this->once())
                    ->method('trans')
                    ->with('key')
                    ->willReturn('value');

                return 'value';
            });

        $this->controller->trans('key');
    }
}
