<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Collection;

/**
 * Lazily hydrate query results.
 */
abstract class AbstractLazyCollection implements \Iterator, \Countable
{
    /**
     * @var \Iterator
     */
    protected $documents;

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $this->initialize();

        return $this->documents->count();
    }

    /**
     * {@inheritDoc}
     */
    abstract public function current();

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        $this->initialize();

        return $this->documents->key();
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->initialize();

        $this->documents->next();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->initialize();

        $this->documents->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        $this->initialize();

        return $this->documents->valid();
    }

    /**
     * Returns a array of all documents in the collection.
     *
     * @return array
     */
    public function toArray()
    {
        $copy = array();
        foreach ($this as $document) {
            $copy[] = $document;
        }

        return $copy;
    }

    /**
     * Initialize the collection documents.
     */
    abstract protected function initialize();
}
