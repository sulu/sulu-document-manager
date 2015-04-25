<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Sulu\Component\DocumentManager\Tests\Unit\Subscriber\Behavior\Mapping;

use Sulu\Component\DocumentManager\Subscriber\Behavior\Mapping\TimestampSubscriber;
use Sulu\Component\DocumentManager\PropertyEncoder;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Behavior\Mapping\TimestampBehavior;
use Prophecy\Argument;
use Sulu\Component\DocumentManager\DocumentAccessor;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use Sulu\Component\DocumentManager\MetadataFactory;
use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\Metadata;
use Sulu\Component\DocumentManager\Document\UnknownDocument;
use Sulu\Component\DocumentManager\Behavior\Mapping\PathBehavior;
use Sulu\Component\DocumentManager\Subscriber\Behavior\Mapping\PathSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use Sulu\Component\DocumentManager\ProxyFactory;
use Sulu\Component\DocumentManager\DocumentInspector;

class PathSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->hydrateEvent = $this->prophesize(HydrateEvent::class);
        $this->document = new TestPathDocument();
        $this->notImplementing = new \stdClass;
        $this->node = $this->prophesize(NodeInterface::class);
        $this->pathNode = $this->prophesize(NodeInterface::class);
        $this->pathDocument = new \stdClass();
        $this->inspector = $this->prophesize(DocumentInspector::class);
        $this->accessor = $this->prophesize(DocumentAccessor::class);
        $this->hydrateEvent->getAccessor()->willReturn($this->accessor);

        $this->subscriber = new PathSubscriber(
            $this->inspector->reveal()
        );
    }

    /**
     * It should return early if the document does not implement the PathBehavior interface
     */
    public function testHydrateNotImplementing()
    {
        $this->hydrateEvent->getDocument()->willReturn($this->notImplementing);
        $this->subscriber->handleHydrate($this->hydrateEvent->reveal());
    }

    /**
     * It should populate the documents path property with a proxy
     */
    public function testHydratePath()
    {
        $this->hydrateEvent->getDocument()->willReturn($this->document);

        $this->node->getPath()->willReturn($this->pathNode->reveal());

        $this->inspector->getPath($this->document)->willReturn('/path/to');
        $this->accessor->set(
            'path', '/path/to'
        )->shouldBeCalled();

        $this->subscriber->handleHydrate($this->hydrateEvent->reveal());
    }
}

class TestPathDocument implements PathBehavior
{
    private $path;

    public function getPath() 
    {
        return $this->path;
    }
}
