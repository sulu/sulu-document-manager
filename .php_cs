<?php

$header = <<<EOF
This file is part of the Sulu CMS.

(c) MASSIVE ART WebServices GmbH

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    ->fixers(array(
        'symfony',
        'concat_with_spaces',
        'ordered_use',
        '-concat_without_spaces',
        '-phpdoc_indent',
        '-phpdoc_params',
        '-psr0',
    ))
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    )
; 
