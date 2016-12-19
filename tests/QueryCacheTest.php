<?php

use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\Cache\QueryCache;
use SimpleSoftwareIO\Cache\QueryCacheBuilder;
use Illuminate\Support\Facades\Facade;

class QueryCacheTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Facade::clearResolvedInstances();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function enable_returns_true_when_a_length_exists()
    {
        $queryCache = new QueryCache(null, 30);

        $this->assertTrue($queryCache->enabled());
    }

    /** @test */
    public function enable_returns_false_when_a_length_does_not_exists()
    {
        $queryCache = new QueryCache(null, 0);

        $this->assertFalse($queryCache->enabled());
    }

    /** @test */
    public function disable_turns_off_caching()
    {
        $queryCache = new QueryCache(null, 30);

        $queryCache->disable();

        $this->assertFalse($queryCache->enabled());
    }

    /** @test */
    public function enable_turns_on_caching()
    {
        $queryCache = new QueryCache(null, 0);

        $queryCache->enable();

        $this->assertTrue($queryCache->enabled());
    }

    /** @test */
    public function length_sets_the_length_to_cache()
    {
        $queryCache = new QueryCache(null, 30);

        $queryCache->length(20);

        $this->assertAttributeEquals(20, 'length', $queryCache);
    }

    /** @test */
    public function cache_is_not_called_when_it_is_disabled()
    {
        $queryCacheBuilder = Mockery::mock(QueryCacheBuilder::class);
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[performQuery]', [null, 0])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        Cache::shouldReceive('remember')
            ->times(0);

        $queryCache->get($queryCacheBuilder);
    }

    /** @test */
    public function cache_is_called_when_it_is_enabled()
    {
        $queryCacheBuilder = Mockery::mock(QueryCacheBuilder::class);
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[generateKey,performQuery]', [null, 30])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        Cache::shouldReceive('getStore');

        Cache::shouldReceive('store->remember')
            ->once();

        $queryCache->get($queryCacheBuilder);
    }

    /** @test */
    public function cache_is_called_on_the_correct_store()
    {
        $queryCacheBuilder = Mockery::mock(QueryCacheBuilder::class);
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[generateKey,performQuery]', ['fooStore', 30])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        Cache::shouldReceive('getStore');

        Cache::shouldReceive('store')
            ->withArgs(['fooStore'])
            ->once()
            ->andReturn(Mockery::self())
            ->shouldReceive('remember');

        $queryCache->get($queryCacheBuilder);
    }

    /** @test */
    public function cache_is_called_with_correct_key_and_length()
    {
        $queryCacheBuilder = Mockery::mock(QueryCacheBuilder::class);
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[generateKey,performQuery]', [null, 30])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        $queryCache->shouldReceive('generateKey')
            ->once()
            ->andReturn('fooKey');

        Cache::shouldReceive('getStore');

        Cache::shouldReceive('store->remember')
            ->withArgs(['fooKey', 30, Mockery::any()])
            ->once();

        $queryCache->get($queryCacheBuilder);
    }

    /** @test */
    public function cache_is_called_with_tags_if_the_driver_supports_tags()
    {
        $queryCacheBuilder = Mockery::mock(QueryCacheBuilder::class);
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[generateKey,performQuery,isTaggable]', [null, 30])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        $queryCache->shouldReceive('isTaggable')
            ->andReturn(true);

        Cache::shouldReceive('store->tags')
            ->once()
            ->andReturn(Mockery::self())
            ->shouldReceive('remember');

        $queryCache->get($queryCacheBuilder);
    }

    /** @test */
    public function cache_is_called_without_tags_if_the_driver_does_not_support_tags()
    {
        $queryCacheBuilder = Mockery::mock(QueryCacheBuilder::class);
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[generateKey,performQuery,isTaggable]', [null, 30])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        $queryCache->shouldReceive('isTaggable')
            ->andReturn(false);

        Cache::shouldReceive('tags')
            ->times(0)
            ->andReturn(Mockery::self())
            ->shouldReceive('store->remember');

        $queryCache->get($queryCacheBuilder);
    }

    /** @test */
    public function cache_is_tagged_with_the_correct_model()
    {
        $queryCacheBuilder = Mockery::mock(QueryCacheBuilder::class);
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[generateKey,performQuery,isTaggable]', [null, 30])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        $queryCacheBuilder->from = 'fooTable';

        $queryCache->shouldReceive('isTaggable')
            ->andReturn(true);

        Cache::shouldReceive('store->tags')
            ->once()
            ->withArgs(['fooTable'])
            ->andReturn(Mockery::self())
            ->shouldReceive('remember');

        $queryCache->get($queryCacheBuilder);
    }

    /** @test */
    public function cache_is_flushed_for_a_model_if_tagging_is_supported()
    {
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[isTaggable]', [null, 30])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        $queryCache->shouldReceive('isTaggable')
            ->andReturn(true);

        Cache::shouldReceive('tags')
            ->once()
            ->withArgs(['fooTable'])
            ->andReturn(Mockery::self())
            ->shouldReceive('flush')
            ->once();

        $queryCache->flush('fooTable');
    }

    /** @test */
    public function cache_is_flushed_for_everything_if_tagging_is_not_supported()
    {
        $queryCacheBuilder = Mockery::mock(QueryCacheBuilder::class);
        $queryCache = Mockery::mock('SimpleSoftwareIO\Cache\QueryCache[isTaggable]', [null, 30])
            ->shouldAllowMockingProtectedMethods()
            ->shouldIgnoreMissing();

        $queryCacheBuilder->from = 'fooTable';

        $queryCache->shouldReceive('isTaggable')
            ->andReturn(false);

        Cache::shouldReceive('tags')
            ->times(0);

        Cache::shouldReceive('flush')
            ->once();

        $queryCache->flush($queryCacheBuilder);
    }
}