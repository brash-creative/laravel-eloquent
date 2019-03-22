<?php

namespace Brash\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CachableQueryBuilder extends AbstractCachable implements CachableQueryBuilderInterface
{
    public function getModel(): Model
    {
        return $this->repository->getModel();
    }

    public function getQuery(): Builder
    {
        return $this->repository->getQuery();
    }

    public function inject(callable $callable): RepositoryInterface
    {
        return $this->repository->inject($callable);
    }

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

    protected function getTable(): string
    {
        return $this->getModel()->getTable();
    }
}
