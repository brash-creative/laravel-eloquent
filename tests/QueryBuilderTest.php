<?php

namespace Brash\Eloquent\Tests;

use Brash\Eloquent\Filter\FilterInterface;
use Brash\Eloquent\Filter\FilterList;
use Brash\Eloquent\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\MockObject\MockObject;


class QueryBuilderTest extends TestCase
{
    /** @var Model|MockObject */
    private $model;

    /** @var Builder|MockObject */
    private $query;

    /** @var Request|MockObject */
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->model = $this->createMock(Model::class);
        $this->query = $this->createMock(Builder::class);
        $this->request = $this->createMock(Request::class);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetQuery()
    {
        $request = Request::create('/test');
        $request->query->set('include', 'with');
        $request->query->set('includeCount', 'withCount');
        $request->query->set('sort', [
            'title' => 'asc'
        ]);
        $request->query->set('filter', [
            'priority' => 1
        ]);

        $filter = $this->createMock(FilterInterface::class);
        $filterList = new FilterList(['priority' => $filter]);

        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('with')
            ->with(['with'])
            ->willReturnSelf();

        $this->query->expects($this->once())
            ->method('withCount')
            ->with(['withCount'])
            ->willReturnSelf();

        $filter->expects($this->once())
            ->method('__invoke')
            ->with($this->query, 1)
            ->willReturn($this->query);

        $sut = new QueryBuilder($this->model, $filterList, $request);
        $sut->inject(function (Builder $query) {
            return $query;
        });

        $result = $sut->getQuery();

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEquals($this->model, $sut->getModel());
    }

    public function testGet()
    {
        $request = Request::create('/test');

        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->mockWithAndCount();

        $this->query->expects($this->once())
            ->method('get')
            ->willReturn(new Collection);

        $sut = new QueryBuilder($this->model, new FilterList, $request);

        $this->assertInstanceOf(Collection::class, $sut->get());
    }

    public function testPaginate()
    {
        $request = Request::create('/test');
        $paginator = $this->createMock(LengthAwarePaginator::class);

        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->mockWithAndCount();

        $this->query->expects($this->once())
            ->method('paginate')
            ->willReturn($paginator);

        $sut = new QueryBuilder($this->model, new FilterList, $request);

        $this->assertInstanceOf(LengthAwarePaginator::class, $sut->paginate());
    }

    public function testCount()
    {
        $request = Request::create('/test');

        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->mockWithAndCount();

        $this->query->expects($this->once())
            ->method('__call')
            ->with('count')
            ->willReturn(1);

        $sut = new QueryBuilder($this->model, new FilterList, $request);

        $this->assertEquals(1, $sut->count());
    }

    private function mockWithAndCount()
    {
        $this->query->expects($this->once())
            ->method('with')
            ->with([])
            ->willReturnSelf();

        $this->query->expects($this->once())
            ->method('withCount')
            ->with([])
            ->willReturnSelf();
    }
}
