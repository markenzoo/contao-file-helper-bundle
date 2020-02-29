Contao File Helper Bundle
===============================


[![Version](http://img.shields.io/packagist/v/markenzoo/contao-file-helper-bundle.svg?style=for-the-badge&label=Latest)](http://packagist.org/packages/markenzoo/contao-file-helper-bundle)
[![Version](http://img.shields.io/packagist/php-v/markenzoo/contao-file-helper-bundle.svg?style=for-the-badge&label=PHP)](http://packagist.org/packages/markenzoo/contao-file-helper-bundle)
[![GitHub issues](https://img.shields.io/github/issues/markenzoo/contao-file-helper-bundle?style=for-the-badge&logo=github)](https://github.com/markenzoo/contao-file-helper-bundle/issues)
[![License](http://img.shields.io/packagist/l/markenzoo/contao-file-helper-bundle?style=for-the-badge&label=License)](http://packagist.org/packages/markenzoo/contao-file-helper-bundle)
[![Build Status](http://img.shields.io/travis/markenzoo/contao-file-helper-bundle/master.svg?style=for-the-badge&logo=travis)](https://travis-ci.org/markenzoo/contao-file-helper-bundle)
[![Downloads](http://img.shields.io/packagist/dt/markenzoo/contao-file-helper-bundle?style=for-the-badge&label=Downloads)](http://packagist.org/packages/markenzoo/contao-file-helper-bundle)

This extension provides useful file helpers for Contao CMS.

<p align="left">
  <a href="https://github.com/markenzoo/contao-file-helper-bundle/">
    <img alt="markenzoo/contao-file-helper-bundle auf Github" src="https://raw.githubusercontent.com/markenzoo/contao-file-helper-bundle/master/assets/contao-file-helper-icon.png" width="200">
  </a>
</p>

Features
--------

 - See where file are being included
 - Edit related modules/elements with one click
 - Manage your files in the contao backend
 - View file meta information
 
Requirements
------------

 - PHP >7.1
 - Contao ~4.4 LTS || ~4.9 LTS
 
 
Install
-------

### Managed edition

When using the managed edition it's pretty simple to install the package. Just search for the package in the
Contao Manager and install it. Alternatively you can use the CLI.  

```bash
# Using the contao manager
$ php contao-manager.phar.php composer require markenzoo/contao-file-helper-bundle

# Using composer directly
$ php composer.phar require markenzoo/contao-file-helper-bundle

# Using global composer installation
$ composer require markenzoo/contao-file-helper-bundle
```

### Symfony application

If you use Contao in a symfony application without contao/manager-bundle, you have to register the bundle manually:

```php

class AppKernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Markenzoo\ContaoFileHelperBundle\ContaoFileHelperBundle()
        ];
    }
}

```

## Note to self

Run the PHP-CS-Fixer and the unit test before you release your bundle:

```bash
vendor/bin/php-cs-fixer fix -v
vendor/bin/phpunit
vendor/bin/psalm
vendor/bin/psalter --issues=all --dry-run
```