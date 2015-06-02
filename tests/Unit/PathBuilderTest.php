<?php

namespace Sulu\Component\DocumentManager\Tests\Unit;

use Sulu\Component\DocumentManager\PathSegmentRegistry;
use Sulu\Component\DocumentManager\PathBuilder;
use PhpBench\Benchmark;

class PathBuilderTest extends \PHPUnit_Framework_TestCase implements Benchmark
{
    private $pathBuilder;

    public function setUp()
    {
        $pathRegistry = new PathSegmentRegistry(array(
            'one' => 'one',
            'two' => 'two',
        ));
        $this->pathBuilder = new PathBuilder($pathRegistry);
    }

    /**
     * @description Build path 1000 times
     * @revs 1000
     * @beforeMethod setUp
     */
    public function benchBuild()
    {
        $this->pathBuilder->build(array('%one%', '%two%', 'four'));
    }

    /**
     * It should build a path
     * Using a combination of tokens and literal values
     */
    public function testBuild()
    {
        $result = $this->pathBuilder->build(array('%one%', '%two%', 'four'));
        $this->assertEquals('/one/two/four', $result);
    }

    /**
     * It should build "/" for an empty array
     */
    public function testBuildEmpty()
    {
        $this->assertEquals('/', $this->pathBuilder->build(array()));
    }

    /**
     * It should build "/" for an array with "/"
     */
    public function testBuildSingleSlash()
    {
        $this->assertEquals('/', $this->pathBuilder->build(array('/')));
    }

    /**
     * It should replace "//" with "/"
     */
    public function testBuildNoDoubleSlash()
    {
        $this->assertEquals('/hello/world', $this->pathBuilder->build(array('hello', '', '', 'world')));
    }
}
