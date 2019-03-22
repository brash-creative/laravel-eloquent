<?php

namespace Brash\Eloquent\Tests;

use Brash\Eloquent\OrderBy;
use Brash\Eloquent\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\MockObject\MockObject;


class RepositoryTest extends TestCase
{
    /** @var Model|MockObject */
    private $model;

    /** @var Builder|MockObject */
    private $query;

    public function setUp()
    {
        parent::setUp();

        $this->model = $this->createMock(Model::class);
        $this->query = $this->createMock(Builder::class);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetQuery()
    {
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

        $sut = new Repository($this->model);
        $sut->with(['with']);
        $sut->withCount(['withCount']);
        $sut->inject(function (Builder $query) {
            return $query;
        });
        $sut->orderBy(new OrderBy);

        $result = $sut->getQuery();

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEquals($this->model, $sut->getModel());
    }

    public function testFind()
    {
        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->mockWithAndCount();

        $this->query->expects($this->once())
            ->method('findOrFail')
            ->with(1)
            ->willReturn(clone $this->model);

        $sut = new Repository($this->model);

        $this->assertInstanceOf(Model::class, $sut->find(1));
    }

    public function testGet()
    {
        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->mockWithAndCount();

        $this->query->expects($this->once())
            ->method('get')
            ->willReturn(new Collection);

        $sut = new Repository($this->model);

        $this->assertInstanceOf(Collection::class, $sut->get());
    }

    public function testPaginate()
    {
        $paginator = $this->createMock(LengthAwarePaginator::class);

        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->mockWithAndCount();

        $this->query->expects($this->once())
            ->method('paginate')
            ->willReturn($paginator);

        $sut = new Repository($this->model);

        $this->assertInstanceOf(LengthAwarePaginator::class, $sut->paginate());
    }

    public function testCount()
    {
        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->mockWithAndCount();

        $this->query->expects($this->once())
            ->method('__call')
            ->with('count')
            ->willReturn(1);

        $sut = new Repository($this->model);

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
