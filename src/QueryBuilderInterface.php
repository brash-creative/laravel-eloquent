<?php

namespace Brash\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface QueryBuilderInterface
{
    /**
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @return Builder
     */
    public function getQuery(): Builder;

    /**
     * @param callable $callable
     *
     * @return QueryBuilderInterface
     */
    public function inject(callable $callable): QueryBuilderInterface;

    /**
     * @param $id
     *
     * @return Model
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
}
