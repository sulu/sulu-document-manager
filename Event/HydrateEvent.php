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
use Sulu\Component\DocumentManager\DocumentAccessor;

class HydrateEvent extends AbstractDocumentNodeEvent
{
    /**
     * @param object $document
     */
    public function __construct(NodeInterface $node, $locale)
    {
        $this->locale = $locale;
        $this->node = $node;
    }

    /**
     * @return object
     */
    public function getDocument()
    {
        if (null === $this->document) {
            throw new \RuntimeException(
                'Trying to retrieve document when no document has been set. An event ' .
                'listener should have set the document.'
            );
        }

        return $this->document;
    }

    /**
     * @param object $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
        $this->accessor = null;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    
}
