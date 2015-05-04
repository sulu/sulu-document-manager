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

class ReorderEvent extends AbstractDocumentEvent
{
    private $destId;
    private $after;

    public function __construct($document, $destId, $after)
    {
        parent::__construct($document);
        $this->destId = $destId;
        $this->after = $after;
    }

    /**
     * {@inheritDoc}
     */
    public function getDebugMessage()
    {
        return sprintf(
            'd:%s did:%s, after:%s',
            $this->document ? spl_object_hash($this->document) : '<no document>',
            $this->destId ?: '<no dest>',
            $this->after ? 'true' : 'false'
        );
    }

    public function getDestId()
    {
        return $this->destId;
    }

    public function getAfter()
    {
        return $this->after;
    }
}
