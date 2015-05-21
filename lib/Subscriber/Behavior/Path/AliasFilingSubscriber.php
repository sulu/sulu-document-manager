<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Subscriber\Behavior\Path;

use Sulu\Component\DocumentManager\Behavior\Path\AliasFilingBehavior;
use Sulu\Component\DocumentManager\DocumentManager;
use Sulu\Component\DocumentManager\MetadataFactoryInterface;
use Sulu\Component\DocumentManager\NodeManager;

/**
 * Automatically set the parent at a pre-determined location.
 */
class AliasFilingSubscriber extends AbstractFilingSubscriber
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @param NodeManager $nodeManager
     * @param DocumentManager $documentManager
     * @param MetadataFactoryInterface $metadataFactory
     * @param string $basePath
     */
    public function __construct(
        NodeManager $nodeManager,
        DocumentManager $documentManager,
        MetadataFactoryInterface $metadataFactory,
        $basePath
    ) {
        parent::__construct($nodeManager, $documentManager, $basePath);
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param object $document
     *
     * @return bool
     */
    protected function supports($document)
    {
        return $document instanceof AliasFilingBehavior;
    }

    /**
     * @param $document
     *
     * @return string
     */
    protected function getParentName($document)
    {
        return $this->metadataFactory->getMetadataForClass(get_class($document))->getAlias();
    }
}
