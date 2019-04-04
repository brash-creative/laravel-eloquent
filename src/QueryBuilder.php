<?php

namespace Brash\Eloquent;

use Brash\Eloquent\Filter\FilterInterface;
use Brash\Eloquent\Filter\FilterList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use function request;

/**
 * Class QueryBuilder
 * @package Brash\Eloquent
 */
class QueryBuilder implements QueryBuilderInterface
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

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param callable $callable
     *
     * @return QueryBuilderInterface
     */
    public function inject(callable $callable): QueryBuilderInterface
    {
        $this->injections[] = $callable;

        return $this;
    }

    /**
     * @param int   $id
     * @param array $columns
     *
     * @return Model
     */
    public function find($id, array $columns = ['*']): Model
    {
        return $this->getQuery()->find($id, $columns);
    }

    /**
     * @param array $columns
     *
     * @return Collection
     */
    public function get(array $columns = ['*']): Collection
    {
        return $this->getQuery()->get($columns);
    }

    /**
     * @param array $columns
     *
     * @return LengthAwarePaginator
     */
    public function paginate(array $columns = ['*']): LengthAwarePaginator
    {
        $limit = $this->request->query->get('limit');

        return $this->getQuery()->paginate($limit, $columns);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->getQuery()->count();
    }

    /**
     * @return Builder
     */
    public function getQuery(): Builder
    {
        $query = (clone $this->model)
            ->newQuery()
            ->with($this->getWith())
            ->withCount($this->getWithCount());

        $this->applyAll($query);

        return $query;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    protected function getWith($key = 'include'): array
    {
        if ($this->request->query->has($key)) {
            $request = $this->request->query->get($key);

            return explode(',', $request);
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getWithCount(): array
    {
        return $this->getWith('includeCount');
    }

    /**
     * @param Builder $builder
     */
    protected function applyAll(Builder $builder)
    {
        $this->applyInjections($builder);
        $this->applyFilters($builder);
        $this->applySort($builder);
    }

    /**
     * @param Builder $builder
     */
    protected function applyInjections(Builder $builder)
    {
        foreach ($this->injections as $injection) {
            $injection($builder);
        }
    }

    /**
     * @param Builder $builder
     */
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

    /**
     * @param Builder $builder
     */
    protected function applySort(Builder $builder)
    {
        $sortArray = (array) $this->request->query->get('sort');

        foreach ($sortArray as $column => $direction) {
            $builder->orderBy($column, $direction);
        }
    }
}
