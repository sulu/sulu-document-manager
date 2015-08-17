<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Tests\Unit\Subscriber\Behavior\Audit;

use PHPCR\NodeInterface;
use Prophecy\Argument;
use Sulu\Component\DocumentManager\Behavior\Audit\TimestampBehavior;
use Sulu\Component\DocumentManager\DocumentAccessor;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\PropertyEncoder;
use Sulu\Component\DocumentManager\Subscriber\Behavior\Audit\TimestampSubscriber;

class TimestampSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PersistEvent
     */
    private $persistEvent;

    /**
     * @var HydrateEvent
     */
    private $hydrateEvent;

    /**
     * @var \stdClass
     */
    private $notImplementing;

    /**
     * @var PropertyEncoder
     */
    private $encoder;

    /**
     * @var NodeInterface
     */
    private $node;

    /**
     * @var DocumentAccessor
     */
    private $accessor;

    /**
     * @var TimestampSubscriber
     */
    private $subscriber;

    /**
     * @var \DateTime
     */
    private $createdDate;

    /**
     * @var \DateTime
     */
    private $changedDate;

    public function setUp()
    {
        $this->persistEvent = $this->prophesize(PersistEvent::class);
        $this->hydrateEvent = $this->prophesize(HydrateEvent::class);
        $this->notImplementing = new \stdClass();
        $this->encoder = $this->prophesize(PropertyEncoder::class);
        $this->node = $this->prophesize(NodeInterface::class);
        $this->accessor = $this->prophesize(DocumentAccessor::class);

        $this->subscriber = new TimestampSubscriber($this->encoder->reveal());

        $this->createdDate = new \DateTime('2015-07-01');
        $this->changedDate = new \DateTime('2015-05-01');
        $this->persistEvent->getNode()->willReturn($this->node);
    }

    /**
     * It should return early if the document is not implementing the behavior.
     */
    public function testPersistNotImplementing()
    {
        $this->persistEvent->getDocument()->willReturn($this->notImplementing);
        $this->subscriber->handlePersist($this->persistEvent->reveal());
    }

    /**
     * It should return early if the locale is null.
     */
    public function testPersistLocaleIsNull()
    {
        $document = new TestDocument();
        $this->persistEvent->getDocument()->willReturn($document);
        $this->persistEvent->getLocale()->willReturn(null);

        $this->node->setProperty()->shouldNotBeCalled();

        $this->subscriber->handlePersist($this->persistEvent->reveal());
    }

    /**
     * It should assign "created" if there is created is actually null.
     */
    public function testPersistCreatedWhenNull()
    {
        $locale = 'fr';
        $document = new TestDocument();

        $this->persistEvent->getLocale()->willReturn($locale);
        $this->persistEvent->getDocument()->willReturn($document);
        $this->encoder->localizedSystemName('created', $locale)->willReturn('prop:created');
        $this->encoder->localizedSystemName('changed', $locale)->willReturn('prop:changed');
        $this->node->setProperty('prop:created', Argument::type('DateTime'))->shouldBeCalled();
        $this->node->setProperty('prop:changed', Argument::type('DateTime'))->shouldBeCalled();

        $this->subscriber->handlePersist($this->persistEvent->reveal());
    }

    /**
     * It should always assign "changed".
     */
    public function testPersistChanged()
    {
        $locale = 'fr';
        $document = new TestDocument($this->createdDate);

        $this->persistEvent->getLocale()->willReturn($locale);
        $this->persistEvent->getDocument()->willReturn($document);
        $this->encoder->localizedSystemName('changed', $locale)->willReturn('prop:changed');
        $this->node->setProperty('prop:changed', Argument::type('DateTime'))->shouldBeCalled();

        $this->subscriber->handlePersist($this->persistEvent->reveal());
    }

    /**
     * It should return early when not implementing.
     */
    public function testHydrateNotImplementing()
    {
        $this->hydrateEvent->getDocument()->willReturn($this->notImplementing);
        $this->subscriber->handleHydrate($this->hydrateEvent->reveal());
    }

    /**
     * It should set the created and updated fields on the document.
     */
    public function testHydrate()
    {
        $locale = 'fr';
        $document = new TestDocument($this->createdDate);

        $this->hydrateEvent->getLocale()->willReturn($locale);
        $this->hydrateEvent->getDocument()->willReturn($document);
        $this->hydrateEvent->getAccessor()->willReturn($this->accessor);
        $this->hydrateEvent->getNode()->willReturn($this->node->reveal());
        $this->encoder->localizedSystemName('created', $locale)->willReturn('prop:created');
        $this->encoder->localizedSystemName('changed', $locale)->willReturn('prop:changed');
        $this->node->getPropertyValueWithDefault('prop:created', null)->willReturn($this->createdDate);
        $this->node->getPropertyValueWithDefault('prop:changed', null)->willReturn($this->changedDate);

        $this->subscriber->handleHydrate($this->hydrateEvent->reveal());

        $this->accessor->set('created', $this->createdDate);
        $this->accessor->set('changed', $this->createdDate);
    }
}

class TestDocument implements TimestampBehavior
{
    private $created;
    private $changed;

    public function __construct(\DateTime $created = null, \DateTime $changed = null)
    {
        $this->created = $created;
        $this->changed = $changed;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getChanged()
    {
        return $this->changed;
    }
}
