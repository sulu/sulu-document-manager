<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Sulu\Component\DocumentManager\Tests\Bootstrap;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$helperSet = new \Symfony\Component\Console\Helper\HelperSet([
    'connection' => new \Jackalope\Tools\Console\Helper\DoctrineDbalHelper(Bootstrap::createDbalConnection()),
]);

$cli = new Application('Sulu Document Manager Test CLI', '0.1');
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);
$cli->addCommands([
    new \Jackalope\Tools\Console\Command\InitDoctrineDbalCommand(),
]);
$cli->run();
