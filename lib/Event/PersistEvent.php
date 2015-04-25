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
use Symfony\Component\EventDispatcher\Event;
use Sulu\Component\DocumentManager\Behavior\TitleBehavior;

class PersistEvent extends AbstractMappingEvent
{
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
     * @return NodeInterface
     * @throws \RuntimeException
     */
    public function getNode()
    {
        if (!$this->node) {

            // TODO: Make this into a helper function
            $title = spl_object_hash($this->document);
            if ($this->document instanceof TitleBehavior) {
                $title .= ' (' . $this->document->getTitle() . ')';
            }

            throw new \RuntimeException(sprintf(
                'Trying to retrieve node when no node has been set. An event ' .
                'listener should have set the node when persisting document "%s"',
                $title
            ));
        }

        return $this->node;
    }
   
}
