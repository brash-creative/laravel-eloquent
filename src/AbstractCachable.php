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
     * @var bool
     */
    private $enabled;

    /**
     * CachableQueryBuilder constructor.
     *
     * @param QueryBuilderInterface $repository
     * @param Cache                 $cache
     * @param float                 $ttl
     * @param Request|null          $request
     * @param bool                  $enabled
     */
    public function __construct(
        QueryBuilderInterface $repository,
        Cache $cache,
        float $ttl = 10,
        ?Request $request = null,
        bool $enabled = true
    ) {
        $this->repository = $repository;
        $this->ttl = $ttl;
        $this->request = $request ?? request();

        if ($enabled) {
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
     * @param string $existingKey
     *
     * @return string
     */
    protected function setQueryCacheKey(string $existingKey = ''): string
    {
        $queryArray = $this->request->query->all();

        if (!empty($queryArray)) {
            $cacheKey = empty($existingKey) ? 'request' : ':request';
            $queryArray = array_dot($queryArray);

            ksort($queryArray);

            foreach ($queryArray as $key => $value) {
                $cacheKey = sprintf('%s:%s,%s', $cacheKey, $key, $value);
            }

            return sprintf('%s%s', $existingKey, $cacheKey);
        }

        return $existingKey;
    }

    /**
     * @param mixed ...$params
     *
     * @return string
     */
    protected function getCacheKey(...$params): string
    {
        $key = sprintf('querybuilder:%s', $this->getTable());

        foreach ($params as $param) {
            if (is_array($param)) {
                sort($param);

                $param = implode('.', $param);
            }

            $key = sprintf('%s:%s', $key, $param);
        }

        $key = $this->setQueryCacheKey($key);

        if ($this->cacheKey) {
            $key = sprintf('%s:%s', $key, $this->cacheKey);
        }

        return md5($key);
    }

    /**
     * @return string
     */
    abstract protected function getTable(): string;
}
