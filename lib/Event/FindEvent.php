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

use Sulu\Component\DocumentManager\Exception\DocumentManagerException;
use Symfony\Component\EventDispatcher\Event;

class FindEvent extends AbstractEvent
{
    private $identifier;
    private $document;
    private $locale;
    private $options = array();

    public function __construct($identifier, $locale, array $options = array())
    {
        $this->identifier = $identifier;
        $this->locale = $locale;
        $this->options = $options;
    }

    public function getDebugMessage()
    {
        return sprintf(
            'i:%s d:%s l:%s',
            $this->identifier,
            $this->document ? spl_object_hash($this->document) : '<no document>',
            $this->locale ? : '<no locale>'
        );
    }

    public function getId()
    {
        return $this->identifier;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getDocument()
    {
        if (!$this->document) {
            throw new DocumentManagerException(sprintf(
                'No document has been set for the findEvent for "%s". An event listener should have done this.',
                $this->identifier
            ));
        }

        return $this->document;
    }

    public function setDocument($document)
    {
        $this->document = $document;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
