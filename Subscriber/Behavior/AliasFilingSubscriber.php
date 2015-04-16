<?php

namespace Sulu\Component\DocumentManager\Subscriber\Behavior;

use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Behavior\FilingBehavior;
use Sulu\Component\DocumentManager\NodeManager;
use Sulu\Component\DocumentManager\DocumentManager;
use Sulu\Component\DocumentManager\MetadataFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\Behavior\AliasFilingBehavior;

/**
 * Automatically set the parnet at a pre-determined location
 */
class AliasFilingSubscriber extends AbstractFilingSubscriber
{
    private $metadataFactory;

    public function __construct(
        NodeManager $nodeManager,
        DocumentManager $documentManager,
        MetadataFactory $metadataFactory,
        $basePath
    )
    {
        parent::__construct($nodeManager, $documentManager, $basePath);
        $this->metadataFactory = $metadataFactory;
    }

    protected function supports($document)
    {
        return $document instanceof AliasFilingBehavior;
    }

    protected function getParentName($document)
    {
        return $this->metadataFactory->getMetadataForClass(get_class($document))->getAlias();
    }
}
