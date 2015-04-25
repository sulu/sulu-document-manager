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

class CopyEvent extends MoveEvent
{
    private $copiedPath;

    public function getCopiedPath()
    {
        return $this->copiedPath;
    }

    public function setCopiedPath($copiedPath)
    {
        $this->copiedPath = $copiedPath;
    }
}
