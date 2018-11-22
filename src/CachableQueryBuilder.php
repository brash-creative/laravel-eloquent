<?php

namespace Brash\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;

class CachableQueryBuilder extends AbstractCachable implements CachableQueryBuilderInterface
{

    /**
     * @var QueryBuilderInterface
     */
    private $queryBuilder;

    /**
     * CachableQueryBuilder constructor.
     *
     * @param QueryBuilderInterface $queryBuilder
     * @param Cache                 $cache
     * @param int                   $ttl
     * @param Request|null          $request
     * @param null|string           $env
     */
    public function __construct(
        QueryBuilderInterface $queryBuilder,
        Cache $cache,
        int $ttl = 10,
        ?Request $request = null,
        ?string $env = null
    ) {
        $this->queryBuilder = $queryBuilder;

        parent::__construct($cache, $ttl, $request, $env);
    }

    public function getModel(): Model
    {
        return $this->queryBuilder->getModel();
    }

    public function getQuery(): Builder
    {
        return $this->queryBuilder->getQuery();
    }

    public function inject(callable $callable): QueryBuilderInterface
    {
        return $this->queryBuilder->inject($callable);
    }

    public function get(): Collection
    {
        if (!$this->cache) {
            return $this->queryBuilder->get();
        }

        $key = $this->getCacheKey('get');

        return $this->cache->remember($key, $this->ttl, function () {
            return $this->queryBuilder->get();
        });
    }

    public function paginate(): LengthAwarePaginator
    {
        if (!$this->cache) {
            return $this->queryBuilder->paginate();
        }

        $key = $this->getCacheKey('paginate');

        return $this->cache->remember($key, $this->ttl, function () {
            return $this->queryBuilder->paginate();
        });
    }

    public function count(): int
    {
        if (!$this->cache) {
            return $this->queryBuilder->count();
        }

        $key = $this->getCacheKey('count');

        return $this->cache->remember($key, $this->ttl, function () {
            return $this->queryBuilder->count();
        });
    }

    protected function getTable(): string
    {
        return $this->getModel()->getTable();
    }
}
