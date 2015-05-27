<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Event;

class ReorderEvent extends AbstractDocumentEvent
{
    /**
     * @var string
     */
    private $destId;

    /**
     * @var bool
     */
    private $after;

    /**
     * @param object $document
     * @param string $destId
     * @param bool $after
     */
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
            '%s did:%s after:%s',
            parent::getDebugMessage(),
            $this->destId ?: '<no dest>',
            $this->after ? 'true' : 'false'
        );
    }

    /**
     * @return string
     */
    public function getDestId()
    {
        return $this->destId;
    }

    /**
     * @return bool
     */
    public function getAfter()
    {
        return $this->after;
    }
}
