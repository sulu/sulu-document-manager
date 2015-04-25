<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Subscriber\Behavior\Mapping;

use Sulu\Component\DocumentManager\Behavior\Mapping\UuidBehavior;
use Sulu\Component\DocumentManager\Event\AbstractMappingEvent;
use Sulu\Component\DocumentManager\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Maps the UUID.
 */
class UuidSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::HYDRATE => 'handleUuid',
            Events::PERSIST => array('handleUuid', '0'),
        );
    }

    public function handleUuid(AbstractMappingEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof UuidBehavior) {
            return;
        }

        $node = $event->getNode();

        $accessor = $event->getAccessor();
        $accessor->set(
            'uuid',
            $node->getIdentifier()
        );
    }
}
