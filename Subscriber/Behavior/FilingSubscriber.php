<?php

namespace Sulu\Component\DocumentManager\Subscriber\Behavior;

use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Behavior\FilingBehavior;
use Sulu\Component\DocumentManager\NodeManager;
use Sulu\Component\DocumentManager\DocumentManager;
use Sulu\Component\DocumentManager\MetadataFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sulu\Component\DocumentManager\Events;

/**
 * Automatically set the parnet at a pre-determined location
 */
class FilingSubscriber implements EventSubscriberInterface
{
    private $basePath;
    private $nodeManager;
    private $documentManager;
    private $metadataFactory;

    public function __construct(
        NodeManager $nodeManager,
        DocumentManager $documentManager,
        MetadataFactory $metadataFactory,
        $basePath
    )
    {
        $this->nodeManager = $nodeManager;
        $this->documentManager = $documentManager;
        $this->metadataFactory = $metadataFactory;
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

        if (!$document instanceof FilingBehavior) {
            return;
        }

        $locale = $event->getLocale();
        $alias = $this->metadataFactory->getMetadataForClass(get_class($document))->getAlias();
        $path = sprintf('%s/%s', $this->basePath, $alias);
        $this->nodeManager->createPath($path);
        $parentDocument = $this->documentManager->find($path, $locale);
        $document->setParent($parentDocument);
    }
}
