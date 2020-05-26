<?php

namespace Encima\Albero\Tests\Unit;

use Encima\Albero\Tests\TestCase;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Encima\Albero\Extensions\Query\Builder  as QueryBuilder;

class QueryBuilderExtensionTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_reorder_by()
    {
        $this->mock(Processor::class);
        $this->mock(ConnectionInterface::class);

        $this->builder = new QueryBuilder(
            app(ConnectionInterface::class),
            new Grammar(),
            app(Processor::class)
        );
        $this->builder->select('*')->from('users')->orderBy('email')->orderBy('age', 'desc')->reOrderBy('full_name', 'asc');
        $this->assertEquals(
            'select * from "users" order by "full_name" asc',
            $this->builder->toSql()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_aggregates_remove_order_by_count()
    {
        $this->partialMock(ConnectionInterface::class, function ($mock) {
            $mock->shouldReceive('select')
                ->once()
                ->with('select count(*) as aggregate from "users"', [], true)
                ->andReturn([['aggregate' => 1]]);
        });
        $this->partialMock(Processor::class, function ($mock) {
            $mock->shouldReceive('processSelect')
                ->once()
                ->andReturnUsing(function ($builder, $results) {
                    return $results;
                });
        });

        $builder = new QueryBuilder(
            app(ConnectionInterface::class),
            new Grammar(),
            app(Processor::class)
        );
        $results = $builder->from('users')->orderBy('age', 'desc')->count();
        $this->assertEquals(1, $results);
    }

    /**
     * @test
     * Created: 2020-05-14
     * Updated: 2020-05-14
     */
    public function it_aggregates_remove_order_by_max(): void
    {
        $this->partialMock(ConnectionInterface::class, function ($mock) {
            $mock->shouldReceive('select')
                ->once()
                ->with('select max("id") as aggregate from "users"', [], true)
                ->andReturn([['aggregate' => 1]]);
        });
        $this->partialMock(Processor::class, function ($mock) {
            $mock->shouldReceive('processSelect')
                ->once()
                ->andReturnUsing(function ($builder, $results) {
                    return $results;
                });
        });

        $builder = new QueryBuilder(
            app(ConnectionInterface::class),
            new Grammar(),
            app(Processor::class)
        );

        $results = $builder->from('users')->orderBy('age', 'desc')->max('id');
        $this->assertEquals(1, $results);
    }

    /**
     * @test
     * Created: 2020-05-14
     * Updated: 2020-05-14
     */
    public function it_aggregates_remove_order_by_min(): void
    {
        $this->partialMock(ConnectionInterface::class, function ($mock) {
            $mock->shouldReceive('select')
                ->once()
                ->with('select min("id") as aggregate from "users"', [], true)
                ->andReturn([['aggregate' => 1]]);
        });
        $this->partialMock(Processor::class, function ($mock) {
            $mock->shouldReceive('processSelect')
                ->once()
                ->andReturnUsing(function ($builder, $results) {
                    return $results;
                });
        });

        $builder = new QueryBuilder(
            app(ConnectionInterface::class),
            new Grammar(),
            app(Processor::class)
        );

        $results = $builder->from('users')->orderBy('age', 'desc')->min('id');
        $this->assertEquals(1, $results);
    }

    /**
     * @test
     * Created: 2020-05-14
     * Updated: 2020-05-14
     */
    public function it_aggregates_remove_order_by_sum(): void
    {
        $this->partialMock(ConnectionInterface::class, function ($mock) {
            $mock->shouldReceive('select')
                ->once()
                ->with('select sum("id") as aggregate from "users"', [], true)
                ->andReturn([['aggregate' => 1]]);
        });
        $this->partialMock(Processor::class, function ($mock) {
            $mock->shouldReceive('processSelect')
                ->once()
                ->andReturnUsing(function ($builder, $results) {
                    return $results;
                });
        });

        $builder = new QueryBuilder(
            app(ConnectionInterface::class),
            new Grammar(),
            app(Processor::class)
        );
        $results = $builder->from('users')->orderBy('age', 'desc')->sum('id');
        $this->assertEquals(1, $results);
    }

    /**
     * @test
     * Created: 2020-05-14
     * Updated: 2020-05-14
     */
    public function it_aggregates_remove_order_by_exists(): void
    {
        $this->partialMock(ConnectionInterface::class, function ($mock) {
            $mock->shouldReceive('select')
                ->once()
                ->with('select exists(select * from "users" order by "age" desc) as "exists"', [], true)
                ->andReturn([['exists' => 1]]);
        });
        $builder = new QueryBuilder(
            app(ConnectionInterface::class),
            new Grammar(),
            app(Processor::class)
        );

        $results = $builder->from('users')->orderBy('age', 'desc')->exists();
        $this->assertTrue($results);
    }
}
