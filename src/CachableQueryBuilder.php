<?php

namespace Brash\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CachableQueryBuilder
 * @package Brash\Eloquent
 */
class CachableQueryBuilder extends AbstractCachable implements CachableQueryBuilderInterface
{
    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->repository->getModel();
    }

    /**
     * @return Builder
     */
    public function getQuery(): Builder
    {
        return $this->repository->getQuery();
    }

    /**
     * @param callable $callable
     *
     * @return QueryBuilderInterface
     */
    public function inject(callable $callable): QueryBuilderInterface
    {
        return $this->repository->inject($callable);
    }

    /**
     * @param $id
     *
     * @return Model
     */
    public function find($id): Model
    {
        if (!$this->cache) {
            return $this->repository->find($id);
        }

        $key = sprintf('find.%s', $id);
        $key = $this->getCacheKey($key);

        return $this->cache->remember($key, $this->ttl, function () use ($id) {
            return $this->repository->find($id);
        });
    }

    /**
     * @return Collection
     */
    public function get(): Collection
    {
        if (!$this->cache) {
            return $this->repository->get();
        }

        $key = $this->getCacheKey('get');

        return $this->cache->remember($key, $this->ttl, function () {
            return $this->repository->get();
        });
    }

    /**
     * @return LengthAwarePaginator
     */
    public function paginate(): LengthAwarePaginator
    {
        if (!$this->cache) {
            return $this->repository->paginate();
        }

        $key = $this->getCacheKey('paginate');

        return $this->cache->remember($key, $this->ttl, function () {
            return $this->repository->paginate();
        });
    }

    /**
     * @return int
     */
    public function count(): int
    {
        if (!$this->cache) {
            return $this->repository->count();
        }

        $key = $this->getCacheKey('count');

        return $this->cache->remember($key, $this->ttl, function () {
            return $this->repository->count();
        });
    }

    /**
     * @return string
     */
    protected function getTable(): string
    {
        return $this->getModel()->getTable();
    }
}
