<?php

declare(strict_types=1);

namespace App\Kernel\Translator;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class LocaleListener implements EventSubscriberInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = $request->headers->get('Content-Language');

        if ($locale) {
            /** @phpstan-ignore-next-line */
            $this->translator->setLocale($locale);
        }
    }
}
