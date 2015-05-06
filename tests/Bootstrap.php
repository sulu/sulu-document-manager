<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Tests;

use Jackalope\RepositoryFactoryDoctrineDBAL;
use PHPCR\SessionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Sulu\Component\DocumentManager\EventDispatcher\DebugEventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Bootstrap
{
    public static function createContainer()
    {
        $logDir = __DIR__ . '/../data/logs';

        if (!file_exists($logDir)) {
            mkdir($logDir);
        }

        $container = new ContainerBuilder();
        $container->set('doctrine_phpcr.default_session', self::createSession());
        $stopwatch = new Stopwatch();
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler($logDir .'/test.log'));

        //$dispatcher = new DebugEventDispatcher($container, $stopwatch, $logger);
        $dispatcher = new ContainerAwareEventDispatcher($container);
        $container->set('sulu_document_manager.event_dispatcher', $dispatcher);

        $config = array(
            'sulu_document_manager.default_locale' => 'en',
            'sulu_document_manager.mapping'=> array(
                'full' => array(
                    'alias' => 'full',
                    'phpcr_type' => 'mix:test',
                    'class' => 'Sulu\Component\DocumentManager\Tests\Functional\Model\FullDocument'
                ),
            ),
            'sulu_document_manager.namespace_mapping' => array(
                'system' => 'nsys',
                'system_localized' => 'lsys',
                'content' => 'ncont',
                'content_localized' => 'lcont',
            ),
        );

        foreach ($config as $parameterName => $parameterValue) {
            $container->setParameter($parameterName, $parameterValue);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../symfony-di'));
        $loader->load('core.xml');
        $loader->load('subscribers.xml');

        foreach (array_keys($container->findTaggedServiceIds('sulu_document_manager.event_subscriber')) as $subscriberId) {
            $def = $container->get($subscriberId);
            $dispatcher->addSubscriberService($subscriberId, get_class($def));
        }

        return $container;
    }

    /**
     * Create a new PHPCR session
     *
     * @return SessionInterface
     */
    public static function createSession()
    {
        $transportName = getenv('SULU_DM_TRANSPORT') ? : 'jackalope-doctrine-dbal';

        switch ($transportName) {
            case 'jackalope-doctrine-dbal':
                return static::createJackalopeDoctrineDbal();
        }

        throw new \InvalidArgumentException(sprintf(
            'Unknown transport "%s"', $transportName
        ));
    }

    public static function createDbalConnection()
    {
        $driver = 'pdo_sqlite'; // pdo_pgsql | pdo_sqlite

        $connection = \Doctrine\DBAL\DriverManager::getConnection(array(
            'driver' => $driver,
            'host' => 'localhost',
            'user' => 'admin',
            'password' => 'admin',
            'path' => __DIR__ . '/../data/test.sqlite'
        ));

        return $connection;
    }

    private static function createJackalopeDoctrineDbal()
    {
        $connection = self::createDbalConnection();

        $factory = new RepositoryFactoryDoctrineDBAL();
        $repository = $factory->getRepository(
            array('jackalope.doctrine_dbal_connection' => $connection)
        );

        $credentials = new \PHPCR\SimpleCredentials(null, null);

        $session = $repository->login($credentials, 'default');

        $nodeTypeManager = $session->getWorkspace()->getNodeTypeManager();
        if (!$nodeTypeManager->hasNodeType('mix:test')) {
            $nodeTypeManager->registerNodeTypesCnd(<<<EOT
<
[mix:test] > mix:referenceable mix
EOT
            , true);
        }

        $namespaceRegistry = $session->getWorkspace()->getNamespaceRegistry();
        $namespaceRegistry->registerNamespace('lsys', 'http://example.com/lsys');
        $namespaceRegistry->registerNamespace('nsys', 'http://example.com/nsys');
        $namespaceRegistry->registerNamespace('lcon', 'http://example.com/lcon');
        $namespaceRegistry->registerNamespace('ncon', 'http://example.com/ncon');

        return $session;
    }
}
