<?php

namespace SimpleSoftwareIO\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class QueryCache
{
    /**
     * The cache store to use.
     *
     * @var string
     */
    protected $store;

    /**
     * The amount of time to store the cache.
     *
     * @var int
     */
    protected $length = 30;

    /**
     * QueryCache constructor.
     *
     * @param string $store
     * @param int $length
     */
    public function __construct($store, $length)
    {
        $this->store = $store;
        $this->length = $length;
    }

    /**
     * Returns the status of the cache.
     *
     * @return bool
     */
    public function enabled()
    {
        return $this->length === 0 ? false : true;
    }

    /**
     * Sets the length of the cache.
     *
     * @param int $minutes
     */
    public function length($minutes)
    {
        $this->length = $minutes;
    }

    /**
     * Enables caching on the model.
     *
     * @param int $minutes
     */
    public function enable($minutes = 30)
    {
        $this->length($minutes);
    }

    /**
     * Disables the cache on the model.
     */
    public function disable()
    {
        $this->length = 0;
    }

    /**
     * Gets the model results.
     *
     * @param QueryCacheBuilder $builder
     * @param array $columns
     * @return Collection
     */
    public function get(QueryCacheBuilder $builder, $columns = ['*'])
    {
        if (! $this->enabled()) return $this->performQuery($builder, $columns);

        $key = $this->generateKey($builder, $columns);

        $cache = $this->getCache($builder);

        return $cache->remember($key, $this->length, function () use ($builder, $columns) {
            return $this->performQuery($builder, $columns);
        });
    }

    /**
     * Gets a Cache instance
     *
     * @return Cache
     */
    protected function getCache(QueryCacheBuilder $builder)
    {
        return $this->isTaggable() ? Cache::store($this->store)->tags($this->getTag($builder)) : Cache::store($this->store);
    }

    /**
     * Determines if the cache store support tagging.
     *
     * @return bool
     */
    protected function isTaggable()
    {
        return Cache::getStore() instanceof TaggableStore;
    }

    /**
     * Performs the query on the model.
     *
     * @param QueryCacheBuilder $builder
     * @param array $columns
     * @return mixed
     */
    protected function performQuery(QueryCacheBuilder $builder, $columns = ['*'])
    {
        return call_user_func([$builder, 'parent::get'], $columns);
    }

    /**
     * Generates the cache key.
     *
     * @param QueryCacheBuilder $builder
     * @param array $columns
     * @return string
     */
    protected function generateKey(QueryCacheBuilder $builder, array $columns)
    {
        $sql = $builder->select($columns)->toSql();
        $whereClause = serialize($builder->getBindings());

        return sha1($sql.$whereClause);
    }

    /**
     * Returns the tag to tag a cache.
     *
     * @param QueryCacheBuilder $builder
     * @return string
     */
    protected function getTag(QueryCacheBuilder $builder)
    {
        return $builder->from;
    }

    /**
     * Flushes the cache for a model.
     *
     * @param $tag
     * @return mixed
     */
    public function flush($tag)
    {
        if ($this->isTaggable()) return Cache::tags($tag)->flush();

        return Cache::flush();
    }
}