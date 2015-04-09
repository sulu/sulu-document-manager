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

abstract class AbstractDocumentNodeEvent extends Event
{
    /**
     * @var object $document
     */
    protected $document;

    /**
     * @var string $locale
     */
    protected $locale;

    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @var AccessorClass
     */
    protected $accessor;

    /**
     * @param object $document
     */
    public function __construct(NodeInterface $node, $locale)
    {
        $this->locale = $locale;
        $this->node = $node;
    }

    /**
     * @return NodeInterface
     */
    public function getNode() 
    {
        return $this->node;
    }

    /**
     * @return object
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return string
     */
    public function getLocale() 
    {
        return $this->locale;
    }

    /**
     * @return DocumentAccessor
     */
    public function getAccessor()
    {
        if ($this->accessor) {
            return $this->accessor;
        }

        $this->accessor = new DocumentAccessor($this->getDocument());

        return $this->accessor;
    }

    /**
     * Return true if the document has been set
     */
    public function hasDocument()
    {
        return null !== $this->document;
    }

    /**
     * @return boolean
     */
    public function hasNode()
    {
        return null !== $this->node;
    }
}
