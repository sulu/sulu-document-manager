<?php

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
