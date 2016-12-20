<?php

namespace SimpleSoftwareIO\Cache;

trait Cacheable
{
    /**
     * Configures the cache store to be used.
     *
     * @var
     */
    protected $cacheStore;

    /**
     * Determines the length to cache a result.
     *
     * @var int
     */
    protected $cacheLength = 30;

    /**
     * Determines if the cache should be busted
     * on inserts/updates/deletes.
     *
     * @var bool
     */
    protected $cacheBusting = false;

    /**
     * Overrides the default QueryBuilder to inject the Cache methods.
     *
     * @return QueryCacheBuilder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new QueryCacheBuilder($this->queryCache(), $conn, $grammar, $conn->getPostProcessor());
    }

    /**
     * Generates a new QueryCache.
     *
     * @return QueryCache
     */
    protected function queryCache()
    {
        return new QueryCache($this->cacheStore, $this->cacheLength);
    }

    /**
     * Flushes the cache on insert/update.
     *
     * @param array $options
     * @return void
     */
    public function finishSave(array $options)
    {
        if ($this->cacheBusting) {
            $this->queryCache()->flush($this->getTable());
        }

        parent::finishSave($options);
    }

    /**
     * Flushes the cache on deletes.
     *
     * @return bool|null
     */
    public function delete()
    {
        if ($this->cacheBusting) {
            $this->queryCache()->flush($this->getTable());
        }

        return parent::delete();
    }

    /**
     * Enables cache busting.
     *
     * @return Cacheable
     */
    public function bust()
    {
        $this->cacheBusting = true;

        return $this;
    }

    /**
     * Disables cache busting.
     *
     * @return Cacheable
     */
    public function dontBust()
    {
        $this->cacheBusting = false;

        return $this;
    }

    /**
     * Returns the status of cache busting.
     *
     * @return bool
     */
    public function isBusting()
    {
        return $this->cacheBusting;
    }
}
