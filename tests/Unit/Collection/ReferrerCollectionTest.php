<?php

namespace Sulu\Comonent\DocumentManager\Tests\Unit\Collection;

use PHPCR\Query\QueryResultInterface;
use Sulu\Component\DocumentManager\Query\ResultCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Events;
use Prophecy\Argument;
use PHPCR\Query\RowInterface;
use Sulu\Component\DocumentManager\Collection\QueryResultCollection;
use Sulu\Component\DocumentManager\Collection\ReferrerCollection;
use PHPCR\PropertyInterface;

class ReferrerCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->reference = $this->prophesize(PropertyInterface::class);
        $this->referrerNode = $this->prophesize(NodeInterface::class);
        $this->node = $this->prophesize(NodeInterface::class);

        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->collection = new ReferrerCollection(
            $this->node->reveal(),
            $this->dispatcher->reveal(),
            'fr'
        );
    }

    /**
     * It should be iterable
     */
    public function testIterable()
    {
        $references = new \ArrayIterator(array(
            $this->reference->reveal()
        ));
        $this->node->getReferences()->willReturn($references);
        $this->reference->getParent()->willReturn($this->referrerNode->reveal());
        $this->referrerNode->getIdentifier()->willReturn('1234');

        $this->dispatcher->dispatch(Events::HYDRATE, Argument::type('Sulu\Component\DocumentManager\Event\HydrateEvent'))->will(function ($args) {
            $args[1]->setDocument(new \stdClass);
        });

        $results = array();

        foreach ($this->collection as $document) {
            $results[] = $document;
        }

        $this->assertCount(1, $results);
        $this->assertContainsOnlyInstancesOf('stdClass', $results);
    }
}
