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
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;

class FindEvent extends Event
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
