<?php

use SimpleSoftwareIO\Cache\QueryCache;
use Illuminate\Database\ConnectionInterface;
use SimpleSoftwareIO\Cache\QueryCacheBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

class QueryCacheBuilderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
        $this->queryCache = Mockery::mock(QueryCache::class);
        $connection = Mockery::mock(ConnectionInterface::class);
        $gramma = Mockery::mock(Grammar::class);
        $processor = Mockery::mock(Processor::class);

        $this->builder = new QueryCacheBuilder($this->queryCache, $connection, $gramma, $processor);
    }

    /** @test */
    public function get_returns_the_queried_results()
    {
        $this->queryCache->shouldReceive('get')
            ->once();

        $this->builder->get();
    }

    /** @test */
    public function remember_enables_caching_with_the_correct_length()
    {
        $this->queryCache->shouldReceive('length')
            ->withArgs(['30'])
            ->once();

        $this->builder->remember(30);
    }

    /** @test */
    public function remember_forever_saves_the_query_for_10_years()
    {
        $this->queryCache->shouldReceive('length')
            ->withArgs(['5256000'])  //10 years in minutes
            ->once();

        $this->builder->rememberForever();
    }

    /** @test */
    public function dont_remember_disables_caching()
    {
        $this->queryCache->shouldReceive('disable')
            ->once();

        $this->builder->dontRemember();
    }
}
