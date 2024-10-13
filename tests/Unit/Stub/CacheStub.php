<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stub;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheStub implements TagAwareCacheInterface
{
    public mixed $cacheValue = null;

    private ItemInterface $item;

    public function __construct(
        private readonly CacheInterface $cache
    ) {
    }

    public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null): mixed
    {
        $this->item->set($callback($this->item, false));

        return $this->cacheValue;
    }

    public function setResult(mixed $result): self
    {
        $this->cacheValue = $result;

        return $this;
    }

    public function getItem(): ?ItemInterface
    {
        return $this->item;
    }

    public function setItem(ItemInterface $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function invalidateTags(array $tags): bool
    {
        $tag = $this->item->tag($tags);

        return (bool) $tag;
    }
}
