<?php

namespace Brash\Eloquent;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class AbstractCachable
{
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
     * @var string
     */
    protected $cacheKey;

    /**
     * CachableQueryBuilder constructor.
     *
     * @param Cache                 $cache
     * @param float                 $ttl
     * @param Request|null          $request
     * @param null|string           $env
     */
    public function __construct(
        Cache $cache,
        float $ttl = 10,
        ?Request $request = null,
        ?string $env = null
    ) {
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

    private function setCache(Cache $cache)
    {
        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($this->getTable());
        }

        $this->cache = $cache;
    }

    protected function getUserCacheKey():? string
    {
        $user = $this->request->user();
        $key = null;

        if ($user instanceof Model && $user->roles instanceof Collection) {
            $key = 'roles:%s';

            $roles = $user->roles->map(function ($item) {
                return $item->name;
            })->all();

            return sprintf($key, implode('.', $roles));
        }

        return $key;
    }

    protected function getQueryCacheKey():? string
    {
        $cacheKey = null;
        $queryArray = $this->request->query->all();

        if (!empty($queryArray)) {
            $cacheKey = 'request';
            $queryArray = array_dot($queryArray);

            ksort($queryArray);

            foreach ($queryArray as $key => $value) {
                $cacheKey = sprintf('%s:%s,%s', $cacheKey, $key, $value);
            }
        }

        return $cacheKey;
    }

    protected function getCacheKey(...$params): string
    {
        $key = sprintf('querybuilder:%s', $this->getTable());
        $key = $this->getUserCacheKey() ? sprintf('%s:%s', $key, $this->getUserCacheKey()) : $key;
        $key = $this->getQueryCacheKey() ? sprintf('%s:%s', $key, $this->getQueryCacheKey()) : $key;

        if ($this->cacheKey) {
            $key = sprintf('%s:%s', $key, $this->cacheKey);
        }

        foreach ($params as $param) {
            $key = sprintf('%s:%s', $key, $param);
        }

        return $key;
    }

    abstract protected function getTable(): string;
}
