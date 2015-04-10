<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Collection;

use PHPCR\Query\QueryResultInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Events;

/**
 * Lazily hydrate query results
 */
abstract class AbstractLazyCollection implements \Iterator, \Countable
{
    protected $elements;

    public function count()
    {
        $this->initialize();
        return $this->elements->count();
    }

    abstract public function current();

    public function key()
    {
        $this->initialize();

        return $this->elements->key();
    }

    public function next()
    {
        $this->initialize();

        return $this->elements->next();
    }

    public function rewind()
    {
        $this->initialize();

        return $this->elements->rewind();
    }

    public function valid()
    {
        $this->initialize();

        return $this->elements->valid();
    }

    public function getArrayCopy()
    {
        $copy = array();
        foreach ($this as $document) {
            $copy[] = $document;
        }

        return $copy;
    }

    abstract protected function initialize();
}

