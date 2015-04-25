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
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Lazily hydrate query results.
 */
class QueryResultCollection extends AbstractLazyCollection
{
    private $eventDispatcher;
    private $result;
    private $locale;

    private $initialized = false;
    private $primarySelector = null;

    public function __construct(QueryResultInterface $result, EventDispatcherInterface $eventDispatcher, $locale, $primarySelector = null)
    {
        $this->result = $result;
        $this->eventDispatcher = $eventDispatcher;
        $this->primarySelector = $primarySelector;
        $this->locale = $locale;
    }

    public function current()
    {
        $this->initialize();
        $row = $this->elements->current();
        $node = $row->getNode($this->primarySelector);

        $hydrateEvent = new HydrateEvent($node, $this->locale);
        $this->eventDispatcher->dispatch(Events::HYDRATE, $hydrateEvent);

        return $hydrateEvent->getDocument();
    }

    protected function initialize()
    {
        if (true === $this->initialized) {
            return;
        }

        $this->elements = $this->result->getRows();
        $this->initialized = true;
    }
}
