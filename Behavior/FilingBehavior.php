<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Behavior;

/**
 * Automatically positions the document at a configured location as a child of a node
 * named after the documents alias.
 *
 * For example, if you specify the base path to be "/cmf/example" and the document has
 * the alias "foobar" then the parent will be set to "/cmf/example/foobar".
 *
 * If the parent document does not exist, it will be created.
 */
interface FilingBehavior extends ParentBehavior
{
}
