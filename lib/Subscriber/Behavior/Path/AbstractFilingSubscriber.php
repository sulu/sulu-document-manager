<?php

namespace Sulu\Component\DocumentManager\Subscriber\Behavior\Path;

use Sulu\Component\DocumentManager\DocumentManager;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\NodeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Automatically set the parent at a pre-determined location.
 */
abstract class AbstractFilingSubscriber implements EventSubscriberInterface
{
    private $basePath;
    private $nodeManager;
    private $documentManager;

    public function __construct(
        NodeManager $nodeManager,
        DocumentManager $documentManager,
        $basePath
    ) {
        $this->nodeManager = $nodeManager;
        $this->documentManager = $documentManager;
        $this->basePath = $basePath;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PERSIST => array('handlePersist', 490),
        );
    }

    public function handlePersist(PersistEvent $event)
    {
        $document = $event->getDocument();

        if (!$this->supports($document)) {
            return;
        }

        $parentName = $this->getParentName($document);
        $path = sprintf('%s/%s', $this->basePath, $parentName);

        $parentNode = $this->nodeManager->createPath($path);
        $event->setParentNode($parentNode);
    }

    /**
     * Return true if this subscriber should be applied to the document.
     *
     * @param object $document
     */
    abstract protected function supports($document);

    /**
     * Return the name of the parent document.
     *
     * @return string
     */
    abstract protected function getParentName($document);
}
