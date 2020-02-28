<?php

declare(strict_types=1);

/*
 * This file is part of markenzoo/contao-file-helper-bundle.
 *
 * Copyright (c) 2020 markenzoo eG
 *
 * @package   markenzoo/contao-file-helper-bundle
 * @author    Felix Kästner <kaestner@markenzoo.de>
 * @copyright 2020 markenzoo eG
 * @license   https://github.com/markenzoo/contao-file-helper-bundle/blob/master/LICENSE MIT License
 */

namespace Markenzoo\ContaoFileHelperBundle\Tests;

use Markenzoo\ContaoFileHelperBundle\ContaoFileHelperBundle;
use PHPUnit\Framework\TestCase;

class ContaoFileHelperBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new ContaoFileHelperBundle();

        $this->assertInstanceOf('Markenzoo\ContaoFileHelperBundle\ContaoFileHelperBundle', $bundle);
    }
}
