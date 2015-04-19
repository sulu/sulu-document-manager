<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Subscriber\Core;

use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sulu\Component\DocumentManager\Event\RemoveEvent;
use Sulu\Component\DocumentManager\DocumentRegistry;
use Symfony\Component\EventDispatcher\Event;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\Event\ClearEvent;
use Sulu\Component\DocumentManager\Event\FindEvent;
use PHPCR\Util\UUIDHelper;

/**
 * Responsible for registering and deregistering documents and PHPCR nodes
 * with the Document Registry
 */
class RegistratorSubscriber implements EventSubscriberInterface
{
    /**
     * @var DocumentRegistry
     */
    private $documentRegistry;

    /**
     * @param DocumentRegistry $documentRegistry
     */
    public function __construct(
        DocumentRegistry $documentRegistry
    )
    {
        $this->documentRegistry = $documentRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::HYDRATE => array(
                array('handleBeginHydrate', 510),
                array('handleHydrate', 490),
                array('handleEndHydrate', -500),
            ),
            Events::PERSIST => array(
                array('handlePersist', 450),
                array('handleNodeFromRegistry', 510),
                array('handleEndPersist', -500),
            ),
            Events::REMOVE => array('handleRemove', 490),
            Events::CLEAR => array('handleClear', 500),
        );
    }

    /**
     * Set the default locale for the hydration request
     * If there is already a document for the node registered, use that.
     *
     * @param HydrateEvent
     */
    public function handleBeginHydrate(HydrateEvent $event)
    {
        // set the default locale
        if (null === $event->getLocale()) {
            $event->setLocale($this->documentRegistry->getDefaultLocale());
        }

        if ($event->hasDocument()) {
            return;
        }

        $node = $event->getNode();

        if (!$this->documentRegistry->hasNode($node)) {
            return;
        }

        $document = $this->documentRegistry->getDocumentForNode($node);
        $locale = $event->getLocale();

        $originalLocale = $this->documentRegistry->getOriginalLocaleForDocument($document);
        $event->setDocument($document);

        if (true === $this->documentRegistry->isHydrated($document) && $originalLocale === $locale) {
            $event->stopPropagation();
            return;
        }

        $this->documentRegistry->updateLocale($document, $locale, $locale);
    }

    public function handleEndHydrate(HydrateEvent $event)
    {
        $this->documentRegistry->markDocumentAsHydrated($event->getDocument());
    }

    public function handleEndPersist(PersistEvent $event)
    {
        $this->documentRegistry->unmarkDocumentAsHydrated($event->getDocument());
    }

    /**
     * If the node for the persisted documnet is in the registry
     *
     * @param PersistEvent
     */
    public function handleNodeFromRegistry(PersistEvent $event) 
    {
        if ($event->hasNode()) {
            return;
        }

        $document = $event->getDocument();

        if (!$this->documentRegistry->hasDocument($document)) {
            return;
        }

        $node = $this->documentRegistry->getNodeForDocument($document);
        $event->setNode($node);
    }

    /**
     * @param HydrateEvent $event
     */
    public function handleHydrate(HydrateEvent $event)
    {
        $this->handleRegister($event);
    }

    /**
     * @param PersistEvent $event
     */
    public function handlePersist(PersistEvent $event)
    {
        $this->handleRegister($event);
    }

    /**
     * @param RemoveEvent
     */
    public function handleRemove(RemoveEvent $event)
    {
        $document = $event->getDocument();
        $this->documentRegistry->deregisterDocument($document);
    }

    public function handleClear(ClearEvent $event)
    {
        $this->documentRegistry->clear();
    }

    private function handleRegister(Event $event)
    {
        $document = $event->getDocument();
        $node = $event->getNode();
        $locale = $event->getLocale();

        if ($this->documentRegistry->hasDocument($document)) {

            $this->documentRegistry->updateLocale($document, $locale);
            return;
        }

        $this->documentRegistry->registerDocument($document, $node, $locale);
    }
}
