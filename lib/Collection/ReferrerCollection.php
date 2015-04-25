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
use PHPCR\NodeInterface;

/**
 * Lazily load documents referring to the given node
 */
class ReferrerCollection extends AbstractLazyCollection
{
    private $dispatcher;
    private $node;
    private $locale;

    private $initialized = false;

    public function __construct(NodeInterface $node, EventDispatcherInterface $dispatcher, $locale)
    {
        $this->node = $node;
        $this->dispatcher = $dispatcher;
        $this->locale = $locale;
        $this->elements = new \ArrayIterator();
    }

    public function current()
    {
        $this->initialize();
        $referrerNode = $this->elements->current();

        $hydrateEvent = new HydrateEvent($referrerNode, $this->locale);
        $this->dispatcher->dispatch(Events::HYDRATE, $hydrateEvent);

        return $hydrateEvent->getDocument();
    }

    protected function initialize()
    {
        if (true === $this->initialized) {
            return;
        }

        $references = $this->node->getReferences();

        // TODO: Performance: calling getParent adds overhead when the collection is
        //       initialized, but if we don't do this, we won't know how many items are in the 
        //       collection, as one node could have many referring properties.
        foreach ($references as $reference) {
            $referrerNode = $reference->getParent();
            $this->elements[$referrerNode->getIdentifier()] = $referrerNode;
        }

        $this->initialized = true;
    }
}

