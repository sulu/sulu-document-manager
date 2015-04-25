<?php

namespace Sulu\Component\DocumentManager\Subscriber\Behavior\Path;

use PHPCR\NodeInterface;
use PHPCR\Util\UUIDHelper;
use Sulu\Component\DocumentManager\Behavior\Path\AutoNameBehavior;
use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\Event\CopyEvent;
use Sulu\Component\DocumentManager\Event\MoveEvent;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;
use Sulu\Component\DocumentManager\Metadata;
use Sulu\Component\DocumentManager\MetadataFactory;
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
    private $metadataFactory;
    private $nodeManager;

    public function __construct(
        DocumentRegistry $registry,
        SlugifierInterface $slugifier,
        MetadataFactory $metadataFactory,
        NameResolver $resolver,
        NodeManager $nodeManager
    ) {
        $this->registry = $registry;
        $this->slugifier = $slugifier;
        $this->metadataFactory = $metadataFactory;
        $this->resolver = $resolver;
        $this->nodeManager = $nodeManager;
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
                'Document of class "%s" has no title (ooid: "%s")',
                get_class($document), spl_object_hash($document)
            ));
        }

        $name = $this->slugifier->slugify($title);
        $parentDocument = $document->getParent();

        if (null === $parentDocument) {
            throw new DocumentManagerException(sprintf(
                'Document with title "%s" has no parent, cannot automatically assing a name',
                $title
            ));
        }

        $parentNode = $this->registry->getNodeForDocument($parentDocument);
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($document));

        $node = $event->hasNode() ? $event->getNode() : null;

        $name = $this->resolver->resolveName($parentNode, $name, $node);

        if (null === $node) {
            $node = $this->createNode($parentNode, $metadata, $name);
            $event->setNode($node);

            return;
        }

        if ($name === $node->getName()) {
            return;
        }

        $node = $event->getNode();
        $defaultLocale = $this->registry->getDefaultLocale();

        if ($defaultLocale == $event->getLocale()) {
            $this->rename($node, $name);
        }
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
     * Create the node, add mixin and set the UUID.
     *
     * TODO: Move this to separate subscriber, it should not be related to AutoName
     *
     * @param NodeInterface $parentNode
     * @param Metadata $metadata
     * @param mixed $name
     */
    private function createNode(NodeInterface $parentNode, Metadata $metadata, $name)
    {
        $node = $parentNode->addNode($name);

        // TODO: Migrate to using primary type
        $node->addMixin($metadata->getPhpcrType());
        $node->setProperty('jcr:uuid', UUIDHelper::generateUUID());

        return $node;
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
