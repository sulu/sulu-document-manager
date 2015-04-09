<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Subscriber\Behavior;

use Sulu\Component\DocumentManager\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\MetadataFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Sulu\Component\DocumentManager\Behavior\ParentBehavior;
use PHPCR\NodeInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use Sulu\Component\DocumentManager\ProxyFactory;

/**
 * Set the parent and children on the doucment
 */
class ParentSubscriber implements EventSubscriberInterface
{
    private $proxyFactory;

    /**
     * @param ProxyFactory $proxyFactory
     */
    public function __construct(ProxyFactory $proxyFactory) {
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::HYDRATE => 'handleHydrate',
        );
    }

    /**
     * @param HydrateEvent $event
     */
    public function handleHydrate(HydrateEvent $event)
    {
        $document = $event->getDocument();
        $node = $event->getNode();

        if (!$document instanceof ParentBehavior) {
            return;
        }

        $this->mapParent($document, $node);
    }

    private function mapParent($document, NodeInterface $node)
    {
        // TODO: performance warning: We are eagerly fetching the parent node
        $targetNode = $node->getParent();
        $document->setParent($this->proxyFactory->createProxyForNode($document, $targetNode));
    }
}
