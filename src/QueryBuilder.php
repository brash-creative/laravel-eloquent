<?php

namespace Brash\Eloquent;

use Brash\Eloquent\Filter\FilterInterface;
use Brash\Eloquent\Filter\FilterList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class QueryBuilder implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $with =[];

    /**
     * @var array
     */
    protected $withCount = [];

    /**
     * @var array
     */
    protected $injections = [];

    /**
     * @var FilterList|FilterInterface[]
     */
    protected $filterList;

    /**
     * @var Request
     */
    protected $request;

    /**
     * QueryBuilderRepository constructor.
     *
     * @param Model   $model
     * @param FilterList|null $filterList
     * @param Request|null    $request
     */
    public function __construct(
        Model $model,
        FilterList $filterList = null,
        Request $request = null
    ) {
        $this->model = $model;
        $this->request = $request ?? request();
        $this->filterList = $filterList ?? new FilterList;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function inject(callable $callable): RepositoryInterface
    {
        $this->injections[] = $callable;

        return $this;
    }

    public function find($id): Model
    {
        return $this->getQuery()->find($id);
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

    public function getQuery(): Builder
    {
        $query = (clone $this->model)
            ->newQuery()
            ->with($this->getWith())
            ->withCount($this->getWithCount());

        $this->applyAll($query);

        return $query;
    }

    protected function getWith($key = 'include'): array
    {
        if ($this->request->query->has($key)) {
            $request = $this->request->query->get($key);

            return explode(',', $request);
        }

        return [];
    }

    protected function getWithCount(): array
    {
        return $this->getWith('includeCount');
    }

    protected function applyAll(Builder $builder)
    {
        $this->applyInjections($builder);
        $this->applyFilters($builder);
        $this->applySort($builder);
    }

    protected function applyInjections(Builder $builder)
    {
        foreach ($this->injections as $injection) {
            $injection($builder);
        }
    }

    protected function applyFilters(Builder $builder)
    {
        $filterArray = (array) $this->request->query->get('filter');

        foreach ($filterArray as $key => $value) {
            if ($this->filterList->has($key)) {
                $filter = $this->filterList->get($key);
                $filter($builder, $value);
            }
        }
    }

    protected function applySort(Builder $builder)
    {
        $sortArray = (array) $this->request->query->get('sort');

        foreach ($sortArray as $column => $direction) {
            $builder->orderBy($column, $direction);
        }
    }

    public function with(array $with): RepositoryInterface
    {
        $this->getQuery()->with($with);

        return $this;
    }

    public function withCount(array $withCount): RepositoryInterface
    {
        $this->getQuery()->withCount($withCount);

        return $this;
    }

    public function orderBy(OrderBy $orderBy): RepositoryInterface
    {
        $this->getQuery()->orderBy($orderBy->getColumn(), $orderBy->getDirection());

        return $this;
    }
}
