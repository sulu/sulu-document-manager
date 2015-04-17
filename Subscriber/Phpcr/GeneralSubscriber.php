<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Subscriber\Phpcr;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sulu\Component\DocumentManager\MetadataFactory;
use Sulu\Component\DocumentManager\NodeManager;
use Sulu\Component\DocumentManager\Event\FindEvent;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;
use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\Event\RemoveEvent;
use Sulu\Component\DocumentManager\Event\MoveEvent;
use Sulu\Component\DocumentManager\Event\CopyEvent;
use Sulu\Component\DocumentManager\Event\ClearEvent;
use Sulu\Component\DocumentManager\Event\FlushEvent;
use Sulu\Component\DocumentManager\Event\RefreshEvent;

/**
 * This class aggregates some basic repository operations.
 *
 * NOTE: If any of these methods need to become more complicated, and
 *       the changes cannot be done by implementing ANOTHER subscriber, then
 *       the individual operations should be broken out into individual subscribers.
 *
 * NOTE: The event dispatcher is added here for the "refresh" method. This is a clear
 *       sign that this should be refactored. The hydration, at least, should be outsourced.
 */
class GeneralSubscriber implements EventSubscriberInterface
{
    /**
     * @var DocumentRegistry
     */
    private $documentRegistry;

    /**
     * @var NodeManager
     */
    private $nodeManager;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(
        DocumentRegistry $documentRegistry,
        NodeManager $nodeManager,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->documentRegistry = $documentRegistry;
        $this->nodeManager = $nodeManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::MOVE => array('handleMove', 500),
            Events::COPY => array('handleCopy', 500),
            Events::CLEAR => array('handleClear', 500),
            Events::FLUSH => array('handleFlush', 500),
            Events::REFRESH => array('handleRefresh', 500),
        );
    }

    public function handleMove(MoveEvent $event)
    {
        $document = $event->getDocument();
        $node = $this->documentRegistry->getNodeForDocument($document);
        $this->nodeManager->move($node->getPath(), $event->getDestId());
    }

    public function handleCopy(CopyEvent $event)
    {
        $document = $event->getDocument();
        $node = $this->documentRegistry->getNodeForDocument($document);
        $newPath = $this->nodeManager->copy($node->getPath(), $event->getDestId());
        $event->setCopiedPath($newPath);
    }

    public function handleRefresh(RefreshEvent $event)
    {
        $document = $event->getDocument();
        $node = $this->documentRegistry->getNodeForDocument($document);
        $locale = $this->documentRegistry->getLocaleForDocument($document);

        // revert/reload the node to the persisted state
        $node->revert();

        // rehydrate the document
        $hydrateEvent = new HydrateEvent($node, $locale);
        $hydrateEvent->setDocument($document);
        $this->eventDispatcher->dispatch(Events::HYDRATE, $hydrateEvent);
    }

    public function handleClear(ClearEvent $event)
    {
        $this->nodeManager->clear();
    }

    public function handleFlush(FlushEvent $event)
    {
        $this->nodeManager->save();
    }
}
