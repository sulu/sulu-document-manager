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
use Sulu\Component\DocumentManager\DocumentInspector;
use Sulu\Component\DocumentManager\Behavior\PathBehavior;

/**
 * Set the path on the document
 */
class PathSubscriber implements EventSubscriberInterface
{
    private $documentInspector;

    /**
     * @param DocumentInspector $documentInspector
     */
    public function __construct(DocumentInspector $documentInspector)
    {
        $this->documentInspector = $documentInspector;
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

        if (!$document instanceof PathBehavior) {
            return;
        }

        $event->getAccessor()->set(
            'path',
            $this->documentInspector->getPath($document)
        );
    }
}

