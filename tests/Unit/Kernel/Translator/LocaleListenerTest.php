<?php

declare(strict_types=1);

namespace App\Tests\Unit\Kernel\Translator;

use App\Kernel\Translator\LocaleListener;
use App\Tests\Unit\Kernel\Mock\Translator\TranslatorMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListenerTest extends TestCase
{
    private MockObject&TranslatorMock $translator;

    private LocaleListener $listener;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorMock::class);
        $this->listener = new LocaleListener($this->translator);
    }

    public function testSubscriberEvents(): void
    {
        $this->assertSame(
            [KernelEvents::REQUEST => ['onKernelRequest', 10]],
            LocaleListener::getSubscribedEvents()
        );
    }

    public function testSetLocaleWhenContentLanguageHeaderIsProvided(): void
    {
        $event = $this->createMock(RequestEvent::class);
        $headerBag = $this->createMock(HeaderBag::class);
        $request = new Request();
        $request->headers = $headerBag;

        $event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $headerBag
            ->expects($this->once())
            ->method('get')
            ->with('Content-Language')
            ->willReturn('en');
        $this->translator
            ->expects($this->once())
            ->method('setLocale')
            ->with('en');

        $this->listener->onKernelRequest($event);
    }
}
