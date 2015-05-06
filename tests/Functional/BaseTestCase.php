<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Tests\Functional;

use Sulu\Component\DocumentManager\Tests\Bootstrap;
use Sulu\Component\DocumentManager\Subscriber\Behavior;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    const BASE_NAME = 'test';
    const BASE_PATH = '/' . self::BASE_NAME;

    private $session;
    private $container;

    protected function initPhpcr()
    {
        $session = $this->getContainer()->get('doctrine_phpcr.default_session');

        if ($session->getRootNode()->hasNode(self::BASE_NAME)) {
            $session->removeItem(self::BASE_PATH);
            $session->save();
        }
    }

    protected function getContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        $this->container = Bootstrap::createContainer();

        return $this->container;
    }

    protected function getDocumentManager()
    {
        return $this->getContainer()->get('sulu_document_manager.document_manager');
    }

    protected function generateDataSet(array $options)
    {
        $options = array_merge(array(
            'locales' => array('en'),
        ), $options);

        $manager = $this->getDocumentManager();
        $document = $manager->create('full');

        foreach ($options['locales'] as $locale) {
            $manager->persist($document, $locale, array(
                'path' => self::BASE_PATH
            ));
        }
    }
}

