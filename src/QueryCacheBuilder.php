<?php

namespace SimpleSoftwareIO\Cache;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

class QueryCacheBuilder extends Builder
{
    /**
     * Holds the QueryCache instance.
     *
     * @var QueryCache
     */
    protected $cache;

    /**
     * QueryCacheBuilder constructor.
     *
     * @param QueryCache $cache
     * @param ConnectionInterface $connection
     * @param Grammar $grammar
     * @param Processor $processor
     */
    public function __construct(QueryCache $cache, ConnectionInterface $connection, Grammar $grammar, Processor $processor)
    {
        $this->cache = $cache;

        parent::__construct($connection, $grammar, $processor);
    }

    /**
     * Returns the query results.
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {
        return $this->cache->get($this, $columns);
    }

    /**
     * Sets the time to remember a query.
     *
     * @param int $minutes
     * @return $this
     */
    public function remember($minutes)
    {
        $this->cache->length($minutes);

        return $this;
    }

    /**
     * Remembers a query forever (10 years).
     *
     * @return $this
     */
    public function rememberForever()
    {
        $this->cache->length(60 * 24 * 365 * 10);

        return $this;
    }

    /**
     * Turns off query caching.
     *
     * @return $this
     */
    public function dontRemember()
    {
        $this->cache->disable();

        return $this;
    }
}
