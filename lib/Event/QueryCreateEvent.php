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
use Sulu\Component\DocumentManager\Query\Query;

class QueryCreateEvent extends AbstractEvent
{
    private $innerQuery;
    private $query;
    private $locale;
    private $primarySelector;

    public function __construct($innerQuery, $locale, $primarySelector = null)
    {
        $this->innerQuery = $innerQuery;
        $this->locale = $locale;
        $this->primarySelector = $primarySelector;
    }

    public function getInnerQuery()
    {
        return $this->innerQuery;
    }

    public function setQuery(Query $query)
    {
        $this->query = $query;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getPrimarySelector()
    {
        return $this->primarySelector;
    }

    public function getQuery()
    {
        if (!$this->query) {
            throw new DocumentManagerException(
                'No query has been set in listener. A listener should have set the query'
            );
        }

        return $this->query;
    }
}
