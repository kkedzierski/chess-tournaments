<?php

declare(strict_types=1);

namespace App\Kernel\Translator;

use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorUXController extends AbstractController
{
    private const ALL = 'ALL';

    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CacheInterface $cache,
        private readonly int $cacheTTl = self::CACHE_TTL,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/trans/{key}', name: 'app_translation', methods: ['GET'])]
    public function trans(string $key): JsonResponse
    {
        $cacheKey = self::ALL === $key ? 'translations_all' : sprintf('translations_%s', $key);

        $translation = $this->cache->get($cacheKey, function (ItemInterface $item) use ($key) {
            $item->expiresAfter($this->cacheTTl);

            /** @phpstan-ignore-next-line */
            return self::ALL === strtoupper($key) ? $this->translator->getCatalogue()->all()['messages'] ?? [] : $this->translator->trans($key);
        });

        return new JsonResponse($translation);
    }
}
