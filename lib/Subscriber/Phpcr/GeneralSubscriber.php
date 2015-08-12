<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Subscriber\Phpcr;

use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\Event\ClearEvent;
use Sulu\Component\DocumentManager\Event\CopyEvent;
use Sulu\Component\DocumentManager\Event\FlushEvent;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Event\MoveEvent;
use Sulu\Component\DocumentManager\Event\RefreshEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\NodeManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        DocumentRegistry $documentRegistry,
        NodeManager $nodeManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->documentRegistry = $documentRegistry;
        $this->nodeManager = $nodeManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MOVE => ['handleMove', 400],
            Events::COPY => ['handleCopy', 400],
            Events::CLEAR => ['handleClear', 500],
            Events::FLUSH => ['handleFlush', 500],
            Events::REFRESH => ['handleRefresh', 500],
        ];
    }

    /**
     * @param MoveEvent $event
     */
    public function handleMove(MoveEvent $event)
    {
        $document = $event->getDocument();
        $node = $this->documentRegistry->getNodeForDocument($document);
        $this->nodeManager->move($node->getPath(), $event->getDestId(), $event->getDestName());
    }

    /**
     * @param CopyEvent $event
     */
    public function handleCopy(CopyEvent $event)
    {
        $document = $event->getDocument();
        $node = $this->documentRegistry->getNodeForDocument($document);
        $newPath = $this->nodeManager->copy($node->getPath(), $event->getDestId(), $event->getDestName());
        $event->setCopiedPath($newPath);
    }

    /**
     * @param RefreshEvent $event
     */
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

    /**
     * @param ClearEvent $event
     */
    public function handleClear(ClearEvent $event)
    {
        $this->nodeManager->clear();
    }

    /**
     * @param FlushEvent $event
     */
    public function handleFlush(FlushEvent $event)
    {
        $this->nodeManager->save();
    }
}
