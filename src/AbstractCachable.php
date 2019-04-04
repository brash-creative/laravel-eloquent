<?php

namespace Brash\Eloquent;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;

abstract class AbstractCachable
{
    /**
     * @var QueryBuilderInterface
     */
    protected $repository;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var Request|null
     */
    protected $request;

    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var array
     */
    protected $withCount = [];

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * CachableQueryBuilder constructor.
     *
     * @param QueryBuilderInterface $repository
     * @param Cache               $cache
     * @param float               $ttl
     * @param Request|null        $request
     * @param null|string         $env
     */
    public function __construct(
        QueryBuilderInterface $repository,
        Cache $cache,
        float $ttl = 10,
        ?Request $request = null,
        ?string $env = null
    ) {
        $this->repository = $repository;
        $this->ttl = $ttl;
        $this->request = $request ?? request();

        $env = $env ?? config('app.env');

        if ($env == 'production') {
            $this->setCache($cache);
        }
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function key(string $key)
    {
        $this->cacheKey = $key;

        return $this;
    }

    /**
     * @param Cache $cache
     */
    private function setCache(Cache $cache): void
    {
        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($this->getTable());
        }

        $this->cache = $cache;
    }

    /**
     * @param mixed ...$params
     *
     * @return string
     */
    protected function getCacheKey(...$params): string
    {
        $key = sprintf('querybuilder:%s', $this->getTable());

        if ($this->cacheKey) {
            $key = sprintf('%s:%s', $key, $this->cacheKey);
        }

        foreach ($params as $param) {
            $key = sprintf('%s:%s', $key, $param);
        }

        return md5($key);
    }

    /**
     * @return string
     */
    abstract protected function getTable(): string;
}
