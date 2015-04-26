<?php

namespace Sulu\Comonent\DocumentManager\tests\Unit\Strategy;

use PHPCR\Strategy\StrategyInterface;
use PHPCR\Strategy\StrategyResultInterface;
use Sulu\Component\DocumentManager\Collection\StrategyResultCollection;
use Sulu\Component\DocumentManager\Event\StrategyExecuteEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\DocumentManager\Strategy\Strategy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sulu\Component\DocumentManager\MetadataFactory;
use Sulu\Component\DocumentManager\Strategy\MixinStrategy;
use Sulu\Component\DocumentManager\Metadata;
use PHPCR\NodeInterface;
use Prophecy\Argument;

class MixinStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->factory = $this->prophesize(MetadataFactory::class);
        $this->metadata = $this->prophesize(Metadata::class);
        $this->strategy = new MixinStrategy($this->factory->reveal());

        $this->document = new \stdClass;
        $this->parentNode = $this->prophesize(NodeInterface::class);
        $this->node = $this->prophesize(NodeInterface::class);
    }

    /**
     * It should create a PHPCR node for the given document and add the
     * PHPCR type as a mixin and set the UUID
     */
    public function testExecutePhpcr()
    {
        $name = 'hello';

        $this->factory->getMetadataForClass('stdClass')->willReturn($this->metadata->reveal());
        $this->metadata->getPhpcrType()->willReturn('someType');
        $this->parentNode->addNode($name)->willReturn($this->node->reveal());
        $this->node->addMixin('someType')->shouldBeCalled();
        $this->node->setProperty('jcr:uuid', Argument::type('string'))->shouldBeCalled();

        $this->strategy->createNodeForDocument($this->document, $this->parentNode->reveal(), $name);
    }

    /**
     * It should resolve metadata for the given PHPCR node
     */
    public function testResolveMetadata()
    {
        $mixinTypes = array('foobar');
        $this->node->getPropertyValue('jcr:mixinTypes')->willReturn($mixinTypes);

        $this->factory->hasMetadataForPhpcrType('foobar')->willReturn(true);
        $this->factory->getMetadataForPhpcrType('foobar')->willReturn($this->metadata->reveal());

        $this->strategy->resolveMetadataForNode($this->node->reveal());
    }
}
