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
        if (isset($this->cacheStore)) {
            return $this->cacheStore;
        }
    }

    /**
     * Returns if the cache should be busted
     * on inserts/updates/delete.
     *
     * @return bool
     */
    public function getCacheBusting()
    {
        if (isset($this->cacheBusting)) {
            return $this->cacheBusting;
        }

        return false;
    }

    /**
     * Gets the length of the cache.  Defaults to 30 minutes.
     *
     * @return int
     */
    public function getCacheLength()
    {
        if (isset($this->cacheLength)) {
            return $this->cacheLength;
        }

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
        return new QueryCache($this->getCacheStore(), $this->getCacheLength());
    }

    /**
     * Flushes the cache on insert/update.
     *
     * @param array $options
     *
     * @return void
     */
    public function finishSave(array $options)
    {
        if ($this->getCacheBusting()) {
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
        if ($this->getCacheBusting()) {
            $this->queryCache()->flush($this->getTable());
        }

        return parent::delete();
    }

    /**
     * Returns the status of cache busting.
     *
     * @return bool
     */
    public function isBusting()
    {
        return $this->getCacheBusting();
    }

    /**
     * Flushes the cache.
     *
     * @return $this
     */
    public function flush()
    {
        $this->queryCache()->flush($this->getTable());

        return $this;
    }
}
