<?php

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

$year = date('Y');

$GLOBALS['ecsHeader'] = <<<EOF
This file is part of markenzoo/contao-file-helper-bundle.

Copyright (c) $year markenzoo eG

@package   markenzoo/contao-file-helper-bundle
@author    Felix Kästner <kaestner@markenzoo.de>
@author    Mathias Arzberger <https://github.com/MDevster>
@copyright $year markenzoo eG
@license   https://github.com/markenzoo/contao-file-helper-bundle/blob/master/LICENSE MIT License
EOF;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__.'/vendor/contao/easy-coding-standard/config/set/contao.php');

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::LINE_ENDING, "\n");

    $services = $containerConfigurator->services();
    $services
        ->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => $GLOBALS['ecsHeader'],
        ]])
    ;
};
