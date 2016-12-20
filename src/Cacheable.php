<?php

namespace SimpleSoftwareIO\Cache;

trait Cacheable
{
    /**
     * Returns the Cache store to use.
     *
     * @return null|string
     */
    public function getCacheStore()
    {
        if (property_exists($this, 'cacheStore')) return $this->cacheStore;

        return null;
    }

    /**
     * Returns if the cache should be busted
     * on inserts/updates/delete.
     *
     * @return bool
     */
    public function getCacheBusting()
    {
        if (property_exists($this, 'cacheBusting')) return $this->cacheBusting;

        return false;
    }

    /**
     * Gets the length of the cache.  Defaults to 30 minutes.
     *
     * @return int
     */
    public function getCacheLength()
    {
        if (property_exists($this, 'cacheLength')) return $this->cacheLength;

        return 30;
    }

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
        return new QueryCache($this->cacheStore, $this->getCacheLength());
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
