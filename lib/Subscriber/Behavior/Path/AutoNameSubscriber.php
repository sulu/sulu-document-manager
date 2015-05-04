<?php

namespace Sulu\Component\DocumentManager\Subscriber\Behavior\Path;

use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Behavior\Path\AutoNameBehavior;
use Sulu\Component\DocumentManager\DocumentHelper;
use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\DocumentStrategyInterface;
use Sulu\Component\DocumentManager\Event\CopyEvent;
use Sulu\Component\DocumentManager\Event\MoveEvent;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;
use Sulu\Component\DocumentManager\NameResolver;
use Sulu\Component\DocumentManager\NodeManager;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Automatically assign a name to the document based on its title.
 *
 * TODO: Refactor MOVE auto-name handling somehow.
 */
class AutoNameSubscriber implements EventSubscriberInterface
{
    private $registry;
    private $slugifier;
    private $nodeManager;
    private $documentStrategy;

    public function __construct(
        DocumentRegistry $registry,
        SlugifierInterface $slugifier,
        NameResolver $resolver,
        NodeManager $nodeManager,
        DocumentStrategyInterface $documentStrategy
    ) {
        $this->registry = $registry;
        $this->slugifier = $slugifier;
        $this->resolver = $resolver;
        $this->nodeManager = $nodeManager;
        $this->documentStrategy = $documentStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PERSIST => array('handlePersist', 480),
            Events::MOVE => array('handleMove', 480),
            Events::COPY => array('handleCopy', 480),
        );
    }

    /**
     * @param MoveEvent $event
     */
    public function handleMove(MoveEvent $event)
    {
        $this->handleMoveCopy($event);
    }

    /**
     * @param CopyEvent
     */
    public function handleCopy(CopyEvent $event)
    {
        $this->handleMoveCopy($event);
    }

    /**
     * @param HydrateEvent $event
     */
    public function handlePersist(PersistEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof AutoNameBehavior) {
            return;
        }

        $title = $document->getTitle();

        if (!$title) {
            throw new DocumentManagerException(sprintf(
                'Document has no title (title is required for auto name behavior): %s)',
                DocumentHelper::getDebugTitle($document)
            ));
        }

        $name = $this->slugifier->slugify($title);

        $parentNode = $event->getParentNode();

        $node = $event->hasNode() ? $event->getNode() : null;
        $name = $this->resolver->resolveName($parentNode, $name, $node);

        if (null === $node) {
            $node = $this->documentStrategy->createNodeForDocument($document, $parentNode, $name);
            $event->setNode($node);

            return;
        }

        if ($name === $node->getName()) {
            return;
        }

        $node = $event->getNode();
        $defaultLocale = $this->registry->getDefaultLocale();

        if ($defaultLocale != $event->getLocale()) {
            return;
        }

        $this->rename($node, $name);
    }

    /**
     * TODO: This is a workaround for a bug in Jackalope which will be fixed in the next
     *       release 1.2: https://github.com/jackalope/jackalope/pull/262.
     */
    private function rename(NodeInterface $node, $name)
    {
        $names = (array) $node->getParent()->getNodeNames();
        $pos = array_search($node->getName(), $names);
        $next = isset($names[$pos + 1]) ? $names[$pos + 1] : null;

        $node->rename($name);

        if ($next) {
            $node->getParent()->orderBefore($name, $next);
        }
    }

    /**
     * Resolve the destination name on move and copy events.
     *
     * @param Event $event
     */
    private function handleMoveCopy(Event $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof AutoNameBehavior) {
            return;
        }

        $destId = $event->getDestId();
        $node = $this->registry->getNodeForDocument($document);
        $destNode = $this->nodeManager->find($destId);
        $nodeName = $this->resolver->resolveName($destNode, $node->getName());

        $event->setDestName($nodeName);
    }
}
