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

class CopyEvent extends MoveEvent
{
    /**
     * @var string
     */
    private $copiedPath;

    /**
     * @return string
     */
    public function getCopiedPath()
    {
        return $this->copiedPath;
    }

    /**
     * @param string $copiedPath
     */
    public function setCopiedPath($copiedPath)
    {
        $this->copiedPath = $copiedPath;
    }
}
