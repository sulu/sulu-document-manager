<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Behavior\Path;

use Sulu\Component\DocumentManager\Behavior\Mapping\TitleBehavior;
use Sulu\Component\DocumentManager\Behavior\Mapping\ParentBehavior;


/**
 * The PHPCR nodes of objects implementing this behavior will have
 * names automatically assigned based on their title.
 */
interface AutoNameBehavior extends TitleBehavior, ParentBehavior
{
}