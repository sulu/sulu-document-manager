<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sulu\Component\DocumentManager\Collection\ChildrenCollection;
use Sulu\Component\DocumentManager\Behavior\ParentBehavior;
use PHPCR\NodeInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use Sulu\Component\DocumentManager\Collection\ReferrerCollection;

/**
 * Handle creation of proxies
 */
class ProxyFactory
{
    private $proxyFactory;
    private $dispatcher;
    private $registry;
    private $metadataFactory;

    /**
     * @param LazyLoadingGhostFactory $proxyFactory
     * @param EventDispatcherInterface $dispatcher
     * @param DocumentRegistry $registry
     * @param MetadataFactory $metadataFactory
     */
    public function __construct(
        LazyLoadingGhostFactory $proxyFactory,
        EventDispatcherInterface $dispatcher,
        DocumentRegistry $registry,
        MetadataFactory $metadataFactory
    ) {
        $this->proxyFactory = $proxyFactory;
        $this->dispatcher = $dispatcher;
        $this->registry = $registry;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * Create a new proxy object from the given document for
     * the given target node.
     *
     * TODO: We only pass the document here in order to correctly evaluate its locale
     *       later. I wonder if it necessary.
     */
    public function createProxyForNode($fromDocument, NodeInterface $targetNode)
    {
        $eventDispatcher = $this->dispatcher;
        $registry = $this->registry;
        $targetMetadata = $this->metadataFactory->getMetadataForPhpcrNode($targetNode);

        // if node is already registered then just return the registered document
        if ($this->registry->hasNode($targetNode)) {
            return $this->registry->getDocumentForNode($targetNode);
        }

        $initializer = function (
            LazyLoadingInterface $document,
            $method,
            array $parameters,
            &$initializer
        ) use (
            $fromDocument,
            $targetNode,
            $eventDispatcher,
            $registry
        ) {
            $locale = $registry->getLocaleForDocument($fromDocument);

            $hydrateEvent = new HydrateEvent($targetNode, $locale);
            $hydrateEvent->setDocument($document);
            $eventDispatcher->dispatch(Events::HYDRATE, $hydrateEvent);

            $initializer = null;
        };

        $proxy = $this->proxyFactory->createProxy($targetMetadata->getClass(), $initializer);
        $locale = $registry->getLocaleForDocument($fromDocument);
        $this->registry->registerDocument($proxy, $targetNode, $locale);

        return $proxy;
    }

    /**
     * Create a new children collection for the given document
     *
     * @param object $document
     *
     * @return ChildrenCollection
     */
    public function createChildrenCollection($document)
    {
        $node = $this->registry->getNodeForDocument($document);
        $locale = $this->registry->getOriginalLocaleForDocument($document);

        return new ChildrenCollection(
            $node,
            $this->dispatcher,
            $locale
        );
    }

    /**
     * Create a new collection of referrers from a list of referencing items.
     *
     * @param object $document
     * @param ItemInterface $items
     *
     * @return ChildrenCollection
     */
    public function createReferrerCollection($document)
    {
        $node = $this->registry->getNodeForDocument($document);
        $locale = $this->registry->getOriginalLocaleForDocument($document);

        return new ReferrerCollection(
            $node,
            $this->dispatcher,
            $locale
        );
    }
}
