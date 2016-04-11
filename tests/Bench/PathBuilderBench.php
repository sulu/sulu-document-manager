<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\tests\Bench;

use Sulu\Component\DocumentManager\PathBuilder;
use Sulu\Component\DocumentManager\PathSegmentRegistry;

/**
 * @Groups({"path_builder"})
 * @Revs(1000)
 * @Iterations(4)
 * @BeforeMethods({"setUp"})
 * @ParamProviders({"provideElements"})
 */
class PathBuilderBench
{
    private $pathBuilder;

    public function setUp()
    {
        $registry = new PathSegmentRegistry([
            'one' => 'hello',
            'two' => 'goodbye',
        ]);
        $this->pathBuilder = new PathBuilder($registry);
    }

    public function benchBuild($params)
    {
        $this->pathBuilder->build($params['elements']);
    }

    public function provideElements()
    {
        return [
            [
                'elements' => ['one', 'two', 'three'],
            ],
            [
                'elements' => ['%one', '%two%', 'three'],
            ],
        ];
    }
}
