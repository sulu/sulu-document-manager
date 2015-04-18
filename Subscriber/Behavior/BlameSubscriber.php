<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Sulu\Component\DocumentManager\Subscriber\Behavior;

use Sulu\Component\DocumentManager\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sulu\Component\DocumentManager\Event\PersistEvent;
use Sulu\Component\DocumentManager\Event\HydrateEvent;
use Sulu\Component\DocumentManager\Behavior\BlameBehavior;
use Sulu\Component\DocumentManager\PropertyEncoder;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Sulu\Component\DocumentManager\Event\AbstractDocumentNodeEvent;
use PHPCR\PropertyType;

/**
 * Manages user blame (log who creator the document and who updated it last)
 */
class BlameSubscriber implements EventSubscriberInterface
{
    const CREATOR = 'creator';
    const CHANGER = 'changer';

    private $encoder;
    private $tokenStorage;

    public function __construct(PropertyEncoder $encoder, TokenStorage $tokenStorage)
    {
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
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
     * @param PersistEvent $event
     */
    public function handlePersist(PersistEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof BlameBehavior) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || $token instanceof AnonymousToken) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            throw new \InvalidArgumentException(sprintf(
                'User must implement the Sulu UserInterface, got "%s"',
                is_object($user) ? get_class($user) : gettype($user)
            ));
        }


        $node = $event->getNode();
        $locale = $event->getLocale();

        if (!$this->getCreator($node, $locale)) {
            $name = $this->encoder->localizedSystemName(self::CREATOR, $locale);
            $node->setProperty($name, $user->getId(), PropertyType::LONG);
        }

        $name = $this->encoder->localizedSystemName(self::CHANGER, $locale);
        $node->setProperty($name, $user->getId(), PropertyType::LONG);

        $this->handleHydrate($event);
    }

    /**
     * @param HydrateEvent $event
     */
    public function handleHydrate(AbstractDocumentNodeEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof BlameBehavior) {
            return;
        }

        $node = $event->getNode();
        $locale = $event->getLocale();
        $accessor = $event->getAccessor();

        $accessor->set(
            self::CREATOR,
            $this->getCreator($node, $locale)
        );

        $accessor->set(
            self::CHANGER,
            $node->getPropertyValueWithDefault(
                $this->encoder->localizedSystemName(self::CHANGER, $locale),
                null
            )
        );
    }

    private function getCreator($node, $locale)
    {
        return $node->getPropertyValueWithDefault(
            $this->encoder->localizedSystemName(self::CREATOR, $locale),
            null
        );
    }
}
