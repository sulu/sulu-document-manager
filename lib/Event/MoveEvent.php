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

use Symfony\Component\EventDispatcher\Event;

class MoveEvent extends AbstractEvent
{
    /**
     * @var object
     */
    private $document;

    /**
     * @var string
     */
    private $destId;

    /**
     * @var string
     */
    private $destName;

    /**
     * @param object $document
     */
    public function __construct($document, $destId)
    {
        $this->document = $document;
        $this->destId = $destId;
    }

    /**
     * {@inheritDoc}
     */
    public function getDebugMessage()
    {
        return sprintf(
            'd:%s did:%s, dnam:%s',
            $this->document ? spl_object_hash($this->document) : '<no document>',
            $this->destId ?: '<no dest>',
            $this->destName ?: '<no dest name>'
        );
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getDestId()
    {
        return $this->destId;
    }

    public function setDestName($name)
    {
        $this->destName = $name;
    }

    public function hasDestName()
    {
        return null !== $this->destName;
    }

    public function getDestName()
    {
        if (!$this->destName) {
            throw new \RuntimeException(sprintf(
                'No destName set in copy/move event when copying/moving document "%s" to "%s" . This should have been set by a listener',
                spl_object_hash($this->document),
                $this->destId
            ));
        }

        return $this->destName;
    }
}
