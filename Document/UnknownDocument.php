<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Sulu\Component\DocumentManager\Document;

use Sulu\Component\DocumentManager\Behavior\ParentBehavior;
use Sulu\Component\DocumentManager\Behavior\NodeNameBehavior;
use Sulu\Component\DocumentManager\Behavior\UuidBehavior;

/**
 * This document class is used when an unmapped node is loaded
 */
class UnknownDocument implements NodeNameBehavior, UuidBehavior
{
    private $nodeName;
    private $uuid;

    /**
     * {@inheritDoc}
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * {@inheritDoc}
     */
    public function getUuid() 
    {
        return $this->uuid;
    }
}
