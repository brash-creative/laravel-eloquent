<?php

namespace Brash\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class CachableRepository extends AbstractCachable implements CachableRepositoryInterface
{
    public function find($id): Model
    {
        if (!$this->cache) {
            return $this->repository->find($id);
        }

        $key = $this->getCacheKey('find', $id);

        return $this->cache->remember($key, $this->ttl, function () use ($id) {
            return $this->repository->find($id);
        });
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

        $key = $this->getCacheKey(
            'paginate',
            sprintf('page:%s', $this->request->query->get('page', 1))
        );

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

    public function with(array $with): RepositoryInterface
    {
        $this->with = $with;
        $this->repository->with($with);

        return $this;
    }

    public function withCount(array $withCount): RepositoryInterface
    {
        $this->withCount = $withCount;
        $this->repository->withCount($withCount);

        return $this;
    }

    public function orderBy(OrderBy $orderBy): RepositoryInterface
    {
        $this->repository->orderBy($orderBy);

        return $this;
    }

    public function inject(callable $callable): RepositoryInterface
    {
        $this->repository->inject($callable);

        return $this;
    }

    public function getModel(): Model
    {
        return $this->repository->getModel();
    }

    public function getQuery(): Builder
    {
        return $this->repository->getQuery();
    }

    public function key(string $key): CachableRepositoryInterface
    {
        $this->cacheKey = $key;

        return $this;
    }

    protected function getTable(): string
    {
        return $this->getModel()->getTable();
    }
}
