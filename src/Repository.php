<?php

namespace Brash\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Repository implements RepositoryInterface
{
    /** @var Model  */
    protected $model;

    /** @var array  */
    protected $with = [];

    /** @var array  */
    protected $withCount = [];

    /** @var array  */
    protected $orderBy = [];

    /** @var array */
    protected $injections = [];

    /**
     * Repository constructor.
     *
     * @param $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find($id): Model
    {
        return $this->getQuery()->findOrFail($id);
    }

    public function get(): Collection
    {
        return $this->getQuery()->get();
    }

    public function paginate(): LengthAwarePaginator
    {
        return $this->getQuery()->paginate();
    }

    public function count(): int
    {
        return $this->getQuery()->count();
    }

    public function with(array $with): RepositoryInterface
    {
        $this->with = $with;

        return $this;
    }

    public function withCount(array $withCount): RepositoryInterface
    {
        $this->withCount = $withCount;

        return $this;
    }

    public function orderBy(OrderBy $orderBy): RepositoryInterface
    {
        $this->inject(function (Builder $query) use ($orderBy) {
            $query->orderBy($orderBy->getColumn(), $orderBy->getDirection());
        });

        return $this;
    }

    public function inject(callable $callable): RepositoryInterface
    {
        $this->injections[] = $callable;

        return $this;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getQuery(): Builder
    {
        $query = (clone $this->model)
            ->newQuery()
            ->with($this->with)
            ->withCount($this->withCount);

        $this->applyInjections($query);

        return $query;
    }

    protected function applyInjections(Builder $query)
    {
        foreach ($this->injections as $injection) {
            $injection($query);
        }
    }
}
