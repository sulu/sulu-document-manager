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

use Sulu\Component\DocumentManager\Event\QueryCreateEvent;
use Sulu\Component\DocumentManager\Query\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PHPCR\SessionInterface;
use Sulu\Component\DocumentManager\Events;
use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\NodeManager;
use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\Event\ReorderEvent;
use PHPCR\Util\UUIDHelper;
use PHPCR\Util\PathHelper;
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;

/**
 * Handles the document reorder operation
 */
class ReorderSubscriber implements EventSubscriberInterface
{
    private $nodeManager;
    private $documentRegistry;

    /**
     * @param NodeManager $nodeManager
     * @param EventDispatcher  $eventDispatcher
     */
    public function __construct(NodeManager $nodeManager, DocumentRegistry $documentRegistry)
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
            Events::REORDER => array('handleReorder', 500),
        );
    }

    /**
     * Handle the reorder operation
     *
     * @param QueryCreateEvent $event
     */
    public function handleReorder(ReorderEvent $event)
    {
        $document = $event->getDocument();
        $siblingId = $event->getDestId();
        $after = $event->getAfter();

        $node = $this->documentRegistry->getNodeForDocument($document);
        $parentNode = $node->getParent();

        $nodeName = $node->getName();
        $siblingName = $this->resolveSiblingName($siblingId, $parentNode, $node);
        if (true === $after) {
            $siblingName = $this->resolveAfterSiblingName($parentNode, $siblingName);
        }

        $parentNode->orderBefore($nodeName, $siblingName);
    }

    private function resolveSiblingName($siblingId, NodeInterface $parentNode, NodeInterface $node)
    {
        if (null === $siblingId) {
            return null;
        }

        $siblingPath = $siblingId;
        if (UUIDHelper::isUUID($siblingId)) {
            $siblingPath = $this->nodeManager->find($siblingId)->getPath();
        }

        if ($siblingPath !== null && PathHelper::getParentPath($siblingPath) !== $parentNode->getPath()) {
            throw new DocumentManagerException(sprintf(
                'Cannot reorder documents which are not siblings. Trying to reorder "%s" to "%s"',
                $node->getPath(), $siblingPath
            ));
        }


        if (null !== $siblingPath) {
            return PathHelper::getNodeName($siblingPath);
        }

        return $node->getName();
    }

    /**
     * If the node should be ordered after the target then we need to order the
     * node before the sibling after the target sibling. If the node should be the
     * last sibling, then the target sibling should be NULL.
     *
     * @param NodeInterface $node
     * @param string $siblingName
     *
     * @return string
     */
    private function resolveAfterSiblingName(NodeInterface $parentNode, $siblingName)
    {
        $targetName = null;
        $found = false;

        foreach (array_keys($parentNode->getNodes()) as $name) {
            if ($name === $siblingName) {
                $found = true;
                continue;
            } elseif ($found) {
                $targetName = $name;
                break;
            }
        }

        return $targetName;
    }
}
