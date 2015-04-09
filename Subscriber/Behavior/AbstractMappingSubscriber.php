<?php

namespace Behavior;

class AbstractMappingSubscriber
{
    /**
     * @var array
     */
    protected $map;

    public function __construct(DocumentAccessor $accessor, PropertyEncoder $encoder)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PERSIST => 'handlePersist',
            Events::HYDRATE => 'handleHydrate',
        );
    }

    /**
     * @param HydrateEvent $event
     */
    public function handleHydrate(HydrateEvent $event)
    {
        $document = $event->getDocument();

        if (!$this->supports($document)) {
            return;
        }

        $this->mapFromNode($node, $document, $accessor);
    }

    /**
     * @param PersistEvent $event
     */
    public function handlePersist(PersistEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof ResourceSegmentBehavior) {
            return;
        }

        $node = $event->getNode();
        $node->setProperty(
            $this->encoder->localizedSystemName(self::URL_FIELD, $event->getLocale()),
            $document->getResourceSegment()
        );
    }
}
