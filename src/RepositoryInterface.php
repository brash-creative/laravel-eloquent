<?php

namespace Brash\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * @param int $id
     *
     * @return Model|object
     * @throws ModelNotFoundException
     */
    public function find($id): Model;

    /**
     * @return Collection
     */
    public function get(): Collection;

    /**
     * @return LengthAwarePaginator
     */
    public function paginate(): LengthAwarePaginator;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param array $with
     *
     * @return Repository
     */
    public function with(array $with): RepositoryInterface;

    /**
     * @param array $withCount
     *
     * @return Repository
     */
    public function withCount(array $withCount): RepositoryInterface;

    /**
     * @param OrderBy $orderBy
     *
     * @return Repository
     */
    public function orderBy(OrderBy $orderBy): RepositoryInterface;

    /**
     * @param callable $callable
     *
     * @return Repository
     */
    public function inject(callable $callable): RepositoryInterface;

    /**
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @return Builder
     */
    public function getQuery(): Builder;
}
