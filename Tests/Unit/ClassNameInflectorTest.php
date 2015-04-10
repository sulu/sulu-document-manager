<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Tests\Unit;

use Sulu\Component\DocumentManager\DocumentRegistry;
use PHPCR\NodeInterface;
use Sulu\Component\DocumentManager\DocumentAccessor;
use Sulu\Component\DocumentManager\ClassNameInflector;

class ClassNameInflectorTest extends \PHPUnit_Framework_TestCase
{
    public function testInflector()
    {
        $this->assertEquals(
            'Hello',
            ClassNameInflector::getUserClassName('Hello')
        );
    }
}

