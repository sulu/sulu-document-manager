<?php

namespace Sulu\Component\DocumentManager\Strategy;

use Sulu\Component\DocumentManager\DocumentStrategyInterface;
use PHPCR\NodeInterface;
use PHPCR\Util\UUIDHelper;
use Sulu\Component\DocumentManager\MetadataFactoryInterface;

/**
 * Manage nodes via. a jcr mixin.
 */
class MixinStrategy implements DocumentStrategyInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createNodeForDocument($document, NodeInterface $parentNode, $name)
    {
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($document));

        $node = $parentNode->addNode($name);
        $node->addMixin($metadata->getPhpcrType());
        $node->setProperty('jcr:uuid', UUIDHelper::generateUUID());

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveMetadataForNode(NodeInterface $node)
    {
        $mixinTypes = (array) $node->getPropertyValue('jcr:mixinTypes');

        foreach ($mixinTypes as $mixinType) {
            if (true == $this->metadataFactory->hasMetadataForPhpcrType($mixinType)) {
                return $this->metadataFactory->getMetadataForPhpcrType($mixinType);
            }
        }

        return null;
    }
}
