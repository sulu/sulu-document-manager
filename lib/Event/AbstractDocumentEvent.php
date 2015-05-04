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

abstract class AbstractDocumentEvent extends AbstractEvent
{
    /**
     * @var object
     */
    private $document;

    /**
     * @param object $document
     */
    public function __construct($document)
    {
        $this->document = $document;
    }

    public function getDocument()
    {
        return $this->document;
    }

    /**
     * {@inheritDoc}
     */
    public function getDebugMessage()
    {
        return sprintf(
            'd:%s',
            $this->document ? spl_object_hash($this->document) : '<no document>'
        );
    }
}
