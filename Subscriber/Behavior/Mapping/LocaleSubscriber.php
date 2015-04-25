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

use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\Behavior\Mapping\LocaleBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Sulu\Component\DocumentManager\Event\AbstractMappingEvent;
use Sulu\Component\DocumentManager\DocumentRegistry;

/**
 * Maps the locale
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    private $registry;

    public function __construct(DocumentRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::HYDRATE => array('handleLocale', 250),
            Events::PERSIST => array('handleLocale', 250),
        );
    }

    public function handleLocale(AbstractMappingEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof LocaleBehavior) {
            return;
        }

        $event->getAccessor()->set(
            'locale',
            $this->registry->getLocaleForDocument($document)
        );
    }
}
