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

use Sulu\Component\DocumentManager\Metadata;
use Sulu\Component\DocumentManager\MetadataFactory;
use Sulu\Component\DocumentManager\Exception\MetadataNotFoundException;
use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Document\UnknownDocument;
use Sulu\Component\DocumentManager\NameResolver;

class NameResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->node = $this->prophesize(NodeInterface::class);
        $this->nameResolver = new NameResolver();
    }

    /**
     * It return the requested name if the parent has no child with the requested name
     */
    public function testGetForClass()
    {
        $this->node->hasNode('foo')->willReturn(false);
        $name = $this->nameResolver->resolveName($this->node->reveal(), 'foo');

        $this->assertEquals('foo', $name);
    }

    /**
     * It should increment the name if the node has a child with the requested name
     */
    public function testGetForClassNotFound()
    {
        $this->node->hasNode('foo')->willReturn(true);
        $this->node->hasNode('foo-1')->willReturn(true);
        $this->node->hasNode('foo-2')->willReturn(false);

        $name = $this->nameResolver->resolveName($this->node->reveal(), 'foo');
        $this->assertEquals('foo-2', $name);
    }
}

