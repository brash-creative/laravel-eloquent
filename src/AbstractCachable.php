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
     * @var RepositoryInterface
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
     * @param RepositoryInterface $repository
     * @param Cache               $cache
     * @param float               $ttl
     * @param Request|null        $request
     * @param null|string         $env
     */
    public function __construct(
        RepositoryInterface $repository,
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

    private function setCache(Cache $cache): void
    {
        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($this->getTable());
        }

        $this->cache = $cache;
    }

    protected function setUserCacheKey(string $existingKey = ''): string
    {
        $user = $this->request->user();

        if ($user instanceof Model && $user->roles instanceof Collection) {
            /** @var array $roles */
            $roles = $user->roles->map(function ($item) {
                return $item->name;
            })->all();

            ksort($roles);

            return sprintf('%s:roles:%s', $existingKey, implode('.', $roles));
        }

        return $existingKey;
    }

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

    protected function setRelationsCacheKey(string $existingKey = ''): string
    {
        $key = '';

        if (!empty($this->with)) {
            $with = $this->with;

            sort($with);

            $key = sprintf(':relations:%s', implode(',', $with));
        }

        return sprintf('%s%s', $existingKey, $key);
    }

    protected function setCountCacheKey(string $existingKey = ''): string
    {
        $key = '';

        if (!empty($this->withCount)) {
            $with = $this->withCount;

            sort($with);

            $key = sprintf(':count:%s', implode(',', $with));
        }

        return sprintf('%s%s', $existingKey, $key);
    }

    protected function setSqlCacheKey(string $existingKey = ''): string
    {
        $key = md5($this->repository->getQuery()->toSql());

        return sprintf('%s:query:%s', $existingKey, $key);
    }

    protected function setBindingsCacheKey(string $existingKey = ''): string
    {
        $key = '';

        if (!empty($this->repository->getQuery()->getBindings())) {
            $bindings = $this->repository->getQuery()->getBindings();

            sort($bindings);

            $key = sprintf(':bindings:%s', implode(',', $bindings));
        }

        return sprintf('%s%s', $existingKey, $key);
    }

    protected function getCacheKey(...$params): string
    {
        $key = sprintf('querybuilder:%s', $this->getTable());
        $key = $this->setUserCacheKey($key);
        $key = $this->setQueryCacheKey($key);
        $key = $this->setRelationsCacheKey($key);
        $key = $this->setCountCacheKey($key);
        $key = $this->setSqlCacheKey($key);
        $key = $this->setBindingsCacheKey($key);

        if ($this->cacheKey) {
            $key = sprintf('%s:%s', $key, $this->cacheKey);
        }

        foreach ($params as $param) {
            $key = sprintf('%s:%s', $key, $param);
        }

        return md5($key);
    }

    abstract protected function getTable(): string;
}
