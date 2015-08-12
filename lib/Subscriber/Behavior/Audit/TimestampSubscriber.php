<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Subscriber\Behavior\Audit;

use Sulu\Component\DocumentManager\Behavior\Audit\TimestampBehavior;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\PropertyEncoder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Manage the timestamp (created, changed) fields on
 * documents before they are persisted.
 */
class TimestampSubscriber implements EventSubscriberInterface
{
    const CREATED = 'created';
    const CHANGED = 'changed';

    /**
     * @var PropertyEncoder
     */
    private $encoder;

    public function __construct(PropertyEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PERSIST => 'handlePersist',
            Events::HYDRATE => 'handleHydrate',
        ];
    }

    /**
     * @param PersistEvent $event
     */
    public function handlePersist(PersistEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof TimestampBehavior) {
            return;
        }

        $locale = $event->getLocale();

        if (!$locale) {
            return;
        }

        $node = $event->getNode();

        if (!$document->getCreated()) {
            $name = $this->encoder->localizedSystemName(self::CREATED, $locale);
            $node->setProperty($name, new \DateTime());
        }

        $name = $this->encoder->localizedSystemName(self::CHANGED, $locale);
        $node->setProperty($name, new \DateTime());
    }

    /**
     * @param HydrateEvent $event
     */
    public function handleHydrate(HydrateEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof TimestampBehavior) {
            return;
        }

        $node = $event->getNode();
        $locale = $event->getLocale();
        $accessor = $event->getAccessor();
        $accessor->set(
            self::CREATED,
            $node->getPropertyValueWithDefault(
                $v = $this->encoder->localizedSystemName(self::CREATED, $locale),
                null
            )
        );

        $accessor->set(
            self::CHANGED,
            $node->getPropertyValueWithDefault(
                $this->encoder->localizedSystemName(self::CHANGED, $locale),
                null
            )
        );
    }
}
