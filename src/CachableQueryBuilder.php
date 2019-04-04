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
     * @param       $id
     * @param array $columns
     *
     * @return Model
     */
    public function find($id, array $columns = ['*']): Model
    {
        if (!$this->cache) {
            return $this->repository->find($id, $columns);
        }

        $key = sprintf('find.%s', $id);
        $key = $this->getCacheKey($key, $columns);

        return $this->cache->remember($key, $this->ttl, function () use ($id, $columns) {
            return $this->repository->find($id, $columns);
        });
    }

    /**
     * @param array $columns
     *
     * @return Collection
     */
    public function get(array $columns = ['*']): Collection
    {
        if (!$this->cache) {
            return $this->repository->get($columns);
        }

        $key = $this->getCacheKey('get', $columns);

        return $this->cache->remember($key, $this->ttl, function () use ($columns) {
            return $this->repository->get($columns);
        });
    }

    /**
     * @param array $columns
     *
     * @return LengthAwarePaginator
     */
    public function paginate(array $columns = ['*']): LengthAwarePaginator
    {
        if (!$this->cache) {
            return $this->repository->paginate($columns);
        }

        $key = $this->getCacheKey('paginate', $columns);

        return $this->cache->remember($key, $this->ttl, function () use ($columns) {
            return $this->repository->paginate($columns);
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
