<?php

use SimpleSoftwareIO\Cache\Cacheable;
use SimpleSoftwareIO\Cache\QueryCache;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    use Cacheable;
}

class CacheableTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function insert_and_update_bust_the_cache_when_busting_is_enabled()
    {
        $model = Mockery::mock('Model[queryCache]')
            ->shouldAllowMockingProtectedMethods();

        $queryCache = Mockery::mock(QueryCache::class);
        $queryCache->shouldReceive('flush')
            ->once();

        $model->shouldReceive('queryCache')
            ->once()
            ->andReturn($queryCache);

        $model->bust();

        $model->finishSave([]);
    }

    /** @test */
    public function insert_and_update_do_not_bust_the_cache_when_busting_is_disabled()
    {
        $model = Mockery::mock('Model[queryCache]')
            ->shouldAllowMockingProtectedMethods();

        $model->shouldReceive('queryCache')
            ->times(0);

        $model->dontBust();

        $model->finishSave([]);
    }

    /** @test */
    public function delete_should_bust_the_cache_when_busting_is_enabled()
    {
        $model = Mockery::mock('Model[queryCache]')
            ->shouldAllowMockingProtectedMethods();

        $queryCache = Mockery::mock(QueryCache::class);
        $queryCache->shouldReceive('flush')
            ->once();

        $model->shouldReceive('queryCache')
            ->once()
            ->andReturn($queryCache);

        $model->bust();

        $model->delete();
    }

    /** @test */
    public function delete_should_not_bust_the_cache_when_busting_is_disabled()
    {
        $model = Mockery::mock('Model[queryCache]')
            ->shouldAllowMockingProtectedMethods();

        $model->shouldReceive('queryCache')
            ->times(0);

        $model->dontBust();

        $model->delete();
    }

    /** @test */
    public function bust_enables_cache_busting()
    {
        $model = new Model;

        $model->bust();

        $this->assertTrue($model->isBusting());
    }

    /** @test */
    public function dont_bust_disables_cache_busting()
    {
        $model = new Model;

        $model->dontBust();

        $this->assertFalse($model->isBusting());
    }
}
