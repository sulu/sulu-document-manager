<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Event;

use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\Behavior\TitleBehavior;
use Symfony\Component\EventDispatcher\Event;
use Sulu\Component\DocumentManager\DocumentHelper;

class PersistEvent extends AbstractMappingEvent
{
    /**
     * @param NodeInterface
     */
    private $parentNode;

    /**
     * @param object $document
     */
    public function __construct($document, $locale, array $options = array())
    {
        $this->document = $document;
        $this->locale = $locale;
        $this->options = $options;
    }

    /**
     * @param NodeInterface $node
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;
    }

    /**
     * @param NodeInterface $node
     */
    public function setParentNode(NodeInterface $parentNode)
    {
        $this->parentNode = $parentNode;
    }

    /**
     * @return NodeInterface
     *
     * @throws \RuntimeException
     */
    public function getNode()
    {
        if (!$this->node) {
            throw new \RuntimeException(sprintf(
                'Trying to retrieve node when no node has been set. An event ' .
                'listener should have set the node when persisting document "%s"',
                DocumentHelper::getDebugTitle($this->document)
            ));
        }

        return $this->node;
    }

    /**
     * @return NodeInterface
     *
     * @throws \RuntimeException
     */
    public function getParentNode()
    {
        if (!$this->parentNode) {
            throw new \RuntimeException(sprintf(
                'Trying to retrieve parent node when no parent node has been set. An event ' .
                'listener should have set the node when persisting document "%s"',
                DocumentHelper::getDebugTitle($this->document)
            ));
        }

        return $this->parentNode;
    }

    /**
     * @return boolean
     */
    public function hasParentNode()
    {
        return $this->parentNode !== null;
    }
    
}
