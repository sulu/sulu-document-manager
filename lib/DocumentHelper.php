<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager;

use Sulu\Component\DocumentManager\Behavior\Mapping\TitleBehavior;

class DocumentHelper
{
    /**
     * Return a debug title for the document for use in exception messages.
     *
     * @return string
     */
    public static function getDebugTitle($document)
    {
        $title = spl_object_hash($document);

        if ($document instanceof TitleBehavior) {
            $title .= ' (' . $document->getTitle() . ')';
        }

        return $title;
    }
}
