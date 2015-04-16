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

use Sulu\Component\DocumentManager\Subscriber\Behavior\FilingSubscriber;
use Sulu\Component\DocumentManager\PropertyEncoder;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Behavior\FilingBehavior;
use Prophecy\Argument;
use Sulu\Component\DocumentManager\DocumentAccessor;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\DocumentManager\NodeManager;
use Sulu\Component\DocumentManager\Metadata;
use Sulu\Component\DocumentManager\MetadataFactory;
use Sulu\Component\DocumentManager\DocumentManager;

class FilingSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->persistEvent = $this->prophesize(PersistEvent::class);
        $this->notImplementing = new \stdClass;
        $this->document = new FilingTestDocument();
        $this->parentDocument = new \stdClass;
        $this->nodeManager = $this->prophesize(NodeManager::class);
        $this->documentManager = $this->prophesize(DocumentManager::class);
        $this->metadataFactory = $this->prophesize(MetadataFactory::class);
        $this->metadata = $this->prophesize(Metadata::class);

        $this->subscriber = new FilingSubscriber(
            $this->nodeManager->reveal(),
            $this->documentManager->reveal(),
            $this->metadataFactory->reveal(),
            '/base/path'
        );

    }

    /**
     * It should return early if the document is not implementing the behavior
     */
    public function testPersistNotImplementing()
    {
        $this->persistEvent->getDocument()->willReturn($this->notImplementing);
        $this->subscriber->handlePersist($this->persistEvent->reveal());
    }

    /**
     * It should set the parent document
     */
    public function testSetParentDocument()
    {
        $this->persistEvent->getDocument()->willReturn($this->document);
        $this->persistEvent->getLocale()->willReturn('fr');
        $this->metadataFactory->getMetadataForClass(FilingTestDocument::class)->willReturn($this->metadata->reveal());
        $this->metadata->getAlias()->willReturn('test');
        $this->nodeManager->createPath('/base/path/test')->shouldBeCalled();
        $this->documentManager->find('/base/path/test', 'fr')->willReturn($this->parentDocument);

        $this->subscriber->handlePersist($this->persistEvent->reveal());

        $this->assertSame($this->parentDocument, $this->document->getParent());
    }

}

class FilingTestDocument implements FilingBehavior
{
    private $parent;

    public function getParent() 
    {
        return $this->parent;
    }
    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
}
