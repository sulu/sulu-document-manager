<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Sulu\Component\DocumentManager\Tests\Unit;

use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\PathSegmentRegistry;
use Sulu\Component\DocumentManager\DocumentInspector;
use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\ProxyFactory;

class DocumentInspectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->documentRegistry = $this->prophesize(DocumentRegistry::class);
        $this->pathRegistry = $this->prophesize(PathSegmentRegistry::class);
        $this->document = new \stdClass;
        $this->node = $this->prophesize(NodeInterface::class);
        $this->proxyFactory = $this->prophesize(ProxyFactory::class);
        $this->documentInspector = new DocumentInspector(
            $this->documentRegistry->reveal(),
            $this->pathRegistry->reveal(),
            $this->proxyFactory->reveal()
        );
    }

    /**
     * It should return the current locale for the given document
     */
    public function testGetLocale()
    {
        $this->documentRegistry->getLocaleForDocument($this->document)->willReturn('de');

        $result = $this->documentInspector->getLocale($this->document);
        $this->assertEquals('de', $result);
    }

    /**
     * It should return the document path
     */
    public function testGetPath()
    {
        $this->documentRegistry->getNodeForDocument($this->document)->willReturn($this->node->reveal());
        $this->node->getPath()->willReturn('/path/to');

        $path = $this->documentInspector->getPath($this->document);
        $this->assertEquals('/path/to', $path);
    }

    /**
     * It should return a PHPCR node
     */
    public function testGetPhpcrNode()
    {
        $this->documentRegistry->getNodeForDocument($this->document)->willReturn($this->node->reveal());

        $result = $this->documentInspector->getNode($this->document);
        $this->assertEquals($this->node->reveal(), $result);
    }

    /**
     * It should return a children
     */
    public function testGetChildren()
    {
        $childrenCollection = new \stdClass;
        $this->proxyFactory->createChildrenCollection($this->document)->willReturn($childrenCollection);
        $this->assertEquals(
            $childrenCollection,
            $this->documentInspector->getChildren($this->document)
        );

    }
}
