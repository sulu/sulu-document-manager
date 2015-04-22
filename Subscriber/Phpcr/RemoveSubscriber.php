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

use PHPCR\PropertyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\NodeManager;
use Sulu\Component\DocumentManager\Event\RemoveEvent;
use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Events;

/**
 * Remove subscriber
 */
class RemoveSubscriber implements EventSubscriberInterface
{
    /**
     * @var DocumentRegistry
     */
    private $documentRegistry;

    /**
     * @var NodeManager
     */
    private $nodeManager;

    public function __construct(
        DocumentRegistry $documentRegistry,
        NodeManager $nodeManager
    )
    {
        $this->documentRegistry = $documentRegistry;
        $this->nodeManager = $nodeManager;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::REMOVE => array('handleRemove', 500),
        );
    }

    /**
     * Remove the given documents node from PHPCR session and optoinally
     * remove any references to the node
     *
     * @param RemoveEvent $event
     */
    public function handleRemove(RemoveEvent $event)
    {
        $document = $event->getDocument();
        $node = $this->documentRegistry->getNodeForDocument($document);

        $node->remove();
    }

    /**
     * Remove references to the given node
     *
     * @param NodeInterface $node
     */
    private function dereference(NodeInterface $node)
    {
        $referrers = $node->getReferences();

        foreach ($referrers as $referrer) {
            if (!$referrer instanceof PropertyInterface) {
                continue;
            }

            $this->dereferenceProperty($node, $referrer);
        }
    }

    /**
     * Remove the given property, or the value which references the node (when
     * multi-valued).
     *
     * @param NodeInterface $node
     * @param PropertyInterface $property
     */
    private function dereferenceProperty(NodeInterface $node, PropertyInterface $property)
    {
        if (false === $property->isMultiple()) {
            $property->remove();
            return;
        }

        // dereference from multi-valued referring properties
        $values = $property->getValue();
        foreach ($values as $i => $referencedNode) {
            if ($referencedNode->getIdentifier() === $node->getIdentifier()) {
                unset($values[$i]);
            }
        }

        $property->getParent()->setProperty($property->getName(), $values);
    }
}
