<?php

namespace Sulu\Component\DocumentManager\Tests\Bench;

use Sulu\Component\DocumentManager\PathBuilder;
use Sulu\Component\DocumentManager\PathSegmentRegistry;
use PhpBench\Benchmark;

class PathBuilderBench implements Benchmark
{
    private $pathBuilder;

    public function setUp()
    {
        $registry = new PathSegmentRegistry(array(
            'one' => 'hello',
            'two' => 'goodbye',
        ));
        $this->pathBuilder = new PathBuilder($registry);
    }
    /**
     * @description Build path 1000 times
     * @revs 1000
     * @beforeMethod setUp
     * @paramProvider provideElements
     */
    public function benchBuild($iteration)
    {
        $this->pathBuilder->build($iteration->getParameter('elements'));
    }

    public function provideElements()
    {
        return array(
            array(
                'elements' => array('one', 'two', 'three')
            ),
            array(
                'elements' => array('%one', '%two%', 'three'),
            ),
        );
    }

}
