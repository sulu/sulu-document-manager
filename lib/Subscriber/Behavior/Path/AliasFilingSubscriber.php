<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Subscriber\Behavior\Path;

use Sulu\Component\DocumentManager\Behavior\Path\AliasFilingBehavior;
use Sulu\Component\DocumentManager\DocumentManager;
use Sulu\Component\DocumentManager\MetadataFactory;
use Sulu\Component\DocumentManager\NodeManager;

/**
 * Automatically set the parnet at a pre-determined location.
 */
class AliasFilingSubscriber extends AbstractFilingSubscriber
{
    private $metadataFactory;

    public function __construct(
        NodeManager $nodeManager,
        DocumentManager $documentManager,
        MetadataFactory $metadataFactory,
        $basePath
    ) {
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
