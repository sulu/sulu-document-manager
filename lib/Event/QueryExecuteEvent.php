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

use Sulu\Component\DocumentManager\Collection\QueryResultCollection;
use Sulu\Component\DocumentManager\Query\Query;
use Symfony\Component\EventDispatcher\Event;

class QueryExecuteEvent extends AbstractEvent
{
    private $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setResult(QueryResultCollection $collection)
    {
        $this->result = $collection;
    }

    public function getResult()
    {
        return $this->result;
    }
}
