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
use Sulu\Component\DocumentManager\Behavior\ChildrenBehavior;
use Sulu\Component\DocumentManager\ProxyFactory;

/**
 * Set the children on the document
 */
class ChildrenSubscriber implements EventSubscriberInterface
{
    private $proxyFactory;

    /**
     * @param ProxyFactory $proxyFactory
     */
    public function __construct(ProxyFactory $proxyFactory)
    {
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

        if (!$document instanceof ChildrenBehavior) {
            return;
        }

        $accessor = $event->getAccessor();
        $accessor->set('children', $this->proxyFactory->createChildrenCollection($document));
    }
}
