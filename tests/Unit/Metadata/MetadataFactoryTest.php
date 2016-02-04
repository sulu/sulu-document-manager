<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\tests\Unit\Metadata;

use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Document\UnknownDocument;
use Sulu\Component\DocumentManager\DocumentStrategyInterface;
use Sulu\Component\DocumentManager\Metadata;
use Sulu\Component\DocumentManager\Metadata\MetadataFactory;
use Sulu\Component\DocumentManager\MetadataFactoryInterface;

class MetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->strategy = $this->prophesize(DocumentStrategyInterface::class);
        $this->baseFactory = $this->prophesize(MetadataFactoryInterface::class);
        $this->factory = new MetadataFactory(
            $this->baseFactory->reveal(),
            $this->strategy->reveal()
        );
    }

    /**
     * It should retrieve metadata for a given PHPCR node.
     */
    public function testGetForPhpcrNode()
    {
        $expectedMetadata = $this->prophesize(Metadata::class);
        $node = $this->prophesize(NodeInterface::class);
        $this->strategy->resolveMetadataForNode($node->reveal())->willReturn($expectedMetadata->reveal());

        $metadata = $this->factory->getMetadataForPhpcrNode($node->reveal());
        $this->assertSame($expectedMetadata->reveal(), $metadata);
    }

    /**
     * It should retrieve return unknown document metadata when node is unmanaged.
     */
    public function testGetForPhpcrNodeNoManaged()
    {
        $node = $this->prophesize(NodeInterface::class);
        $node->hasProperty('jcr:mixinTypes')->willReturn(true);
        $node->getPropertyValue('jcr:mixinTypes')->willReturn([
        ]);

        $metadata = $this->factory->getMetadataForPhpcrNode($node->reveal());
        $this->assertNull($metadata->getAlias());
        $this->assertEquals(UnknownDocument::class, $metadata->getClass());
        $this->assertNull($metadata->getPhpcrType());
    }
}
