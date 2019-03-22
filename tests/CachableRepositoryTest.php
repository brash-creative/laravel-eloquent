<?php

namespace Brash\Eloquent\Tests;

use Brash\Eloquent\CachableQueryBuilder;
use Brash\Eloquent\Filter\FilterList;
use Brash\Eloquent\QueryBuilder;
use Brash\Eloquent\QueryBuilderInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Class CachableRepositoryTest
 * @package Brash\Eloquent\Tests
 * @group CachableRepositoryTest
 */
class CachableRepositoryTest extends TestCase
{
    /** @var Model|MockObject */
    private $model;

    /** @var Builder|MockObject */
    private $query;

    /** @var QueryBuilderInterface|MockObject */
    private $queryBuilder;

    /** @var Cache|MockObject */
    private $cache;

    /** @var Request|MockObject */
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->model = $this->createMock(Model::class);
        $this->query = $this->createMock(Builder::class);
        $this->queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $this->cache = $this->createMock(Cache::class);
        $this->request = $this->createMock(Request::class);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGet()
    {
        $request = Request::create('/test?filter[score]=10&include=user,posts');

        $this->model->expects($this->once())
            ->method('newQuery')
            ->willReturn($this->query);

        $this->mockWithAndCount();

        $this->query->expects($this->once())
            ->method('get')
            ->willReturn(new Collection);

        $sut = new CachableQueryBuilder($this->queryBuilder, $this->cache, 10, $request, 'production');

        $this->assertInstanceOf(Collection::class, $sut->get());
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
