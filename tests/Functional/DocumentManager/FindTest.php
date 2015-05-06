<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Tests\Functional\DocumentManager;

use Sulu\Component\DocumentManager\Tests\Functional\BaseTestCase;
use Sulu\Component\DocumentManager\Tests\Functional\Model\FullDocument;

class DocumentManagerTest extends BaseTestCase
{
    public function setUp()
    {
        $this->initPhpcr();
    }

    /**
     * Persist a document in a single locale
     */
    public function testPersist()
    {
        $this->generateDataSet(array(
            'locales' => array('en'),
        ));

        $manager = $this->getDocumentManager();
        $manager->flush();

        $document = $manager->find(self::BASE_PATH);
        $this->assertNotNull($document);
    }

    /**
     * Persist a document in a many locales
     */
    public function testPersistManyLocales()
    {
        $this->generateDataSet(array(
            'locales' => array('en', 'de'),
        ));

        $manager = $this->getDocumentManager();
        $manager->flush();

        $document = $manager->find(self::BASE_PATH);
        $this->assertNotNull($document);
    }
}
