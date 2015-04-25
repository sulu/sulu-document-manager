<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\DocumentManager\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigureOptionsEvent extends Event
{
    private $options;

    public function __construct(OptionsResolverInterface $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
