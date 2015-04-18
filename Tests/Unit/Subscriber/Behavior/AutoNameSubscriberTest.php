<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Sulu\Component\DocumentManager\Tests\Unit\Subscriber\Behavior;

use Sulu\Component\DocumentManager\DocumentRegistry;
use Sulu\Component\DocumentManager\MetadataFactory;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Behavior\AutoNameBehavior;
use Sulu\Component\DocumentManager\Metadata;
use Sulu\Component\DocumentManager\Subscriber\Behavior\AutoNameSubscriber;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use PHPCR\NodeInterface;
use Prophecy\Argument;
use Sulu\Component\DocumentManager\NameResolver;

class AutoNameSubscriberTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_LOCALE = 'en';

    public function setUp()
    {
        $this->documentRegistry = $this->prophesize(DocumentRegistry::class);
        $this->slugifier = $this->prophesize(SlugifierInterface::class);
        $this->metadataFactory = $this->prophesize(MetadataFactory::class);
        $this->event = $this->prophesize(PersistEvent::class);
        $this->document = $this->prophesize(AutoNameBehavior::class);
        $this->parentDocument = new \stdClass;
        $this->newNode = $this->prophesize(NodeInterface::class);
        $this->node = $this->prophesize(NodeInterface::class);
        $this->parentNode = $this->prophesize(NodeInterface::class);
        $this->metadata = $this->prophesize(Metadata::class);
        $this->parent = new \stdClass;
        $this->documentRegistry->getDefaultLocale()->willReturn(self::DEFAULT_LOCALE);
        $this->resolver = $this->prophesize(NameResolver::class);

        $this->subscriber = new AutoNameSubscriber(
            $this->documentRegistry->reveal(),
            $this->slugifier->reveal(),
            $this->metadataFactory->reveal(),
            $this->resolver->reveal()
        );
    }

    /**
     * It should return early if the document is not an instance of AutoName behavior
     */
    public function testNotInstanceOfAutoName()
    {
        $document = new \stdClass;
        $this->event->hasNode()->willReturn(false);
        $this->event->getDocument()->willReturn($document);
        $this->subscriber->handlePersist($this->event->reveal());
    }

    /**
     * It should throw an exception if the document has no title
     *
     * @expectedException Sulu\Component\DocumentManager\Exception\DocumentManagerException
     */
    public function testNoTitle()
    {
        $this->event->hasNode()->willReturn(false);
        $this->document->getTitle()->willReturn(null);
        $this->event->getDocument()->willReturn($this->document->reveal());
        $this->subscriber->handlePersist($this->event->reveal());
    }

    /**
     * It should throw an exception if the document has no parent
     *
     * @expectedException Sulu\Component\DocumentManager\Exception\DocumentManagerException
     */
    public function testNoParent()
    {
        $this->event->hasNode()->willReturn(false);
        $this->document->getTitle()->willReturn('hai');
        $this->document->getParent()->willReturn(null);
        $this->event->getDocument()->willReturn($this->document->reveal());
        $this->subscriber->handlePersist($this->event->reveal());
    }

    /**
     * It should assign a name based on the documents title
     */
    public function testAutoName()
    {
        $this->doTestAutoName('hai', 'hai', true);
        $this->event->hasNode()->willReturn(false);
        $this->subscriber->handlePersist($this->event->reveal());
    }

    /**
     * It should rename the node if the document is being saved in the default locale
     */
    public function testAlreadyHasNode()
    {
        $this->event->getNode()->willReturn($this->node->reveal());
        $this->event->getLocale()->willReturn(self::DEFAULT_LOCALE);
        $this->event->hasNode()->willReturn(true);
        $this->doTestAutoName('hai-bye', 'hai-2');
        $this->node->rename('hai-bye')->shouldBeCalled();
        $this->node->hasNode()->willReturn(true);

        $this->subscriber->handlePersist($this->event->reveal());
    }

    /**
     * It should not rename the node if the document is being saved a non-default locale
     */
    public function testAlreadyHasNodeNonDefaultLocale()
    {
        $this->event->getNode()->willReturn($this->node->reveal());
        $this->event->getLocale()->willReturn('ay');
        $this->event->hasNode()->willReturn(true);
        $this->doTestAutoName('hai-bye', 'hai-2');
        $this->node->rename('hai-bye')->shouldNotBeCalled();
        $this->node->hasNode()->willReturn(true);

        $this->subscriber->handlePersist($this->event->reveal());
    }

    private function doTestAutoName($title, $expectedName, $create = false)
    {
        $phpcrType = 'sulu:test';
        $this->document->getTitle()->willReturn($title);
        $this->document->getParent()->willReturn($this->parent);
        $this->event->getDocument()->willReturn($this->document->reveal());
        $this->slugifier->slugify($title)->willReturn($title);
        $this->resolver->resolveName($this->parentNode->reveal(), $title)->willReturn($title);
        $this->documentRegistry->getNodeForDocument($this->parent)->willReturn($this->parentNode->reveal());
        $this->metadataFactory->getMetadataForClass(get_class($this->document->reveal()))->willReturn($this->metadata->reveal());

        if (!$create) {
            return;
        }
        $this->parentNode->addNode($expectedName)->willReturn($this->newNode->reveal());
        $this->metadata->getPhpcrType()->willReturn($phpcrType);
        $this->newNode->addMixin($phpcrType)->shouldBeCalled();
        $this->newNode->setProperty('jcr:uuid', Argument::type('string'))->shouldBeCalled();
        $this->event->setNode($this->newNode->reveal())->shouldBeCalled();
    }
}
