# FlexModel

[![Latest version on Packagist][ico-version]][link-version]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-coverage]][link-coverage]
[![SensioLabsInsight][ico-security]][link-security]

An ORM agnostic model (object / view / form) configuration library.

## Installation using Composer

Run the following command to add the package to the composer.json of your project:

``` bash
$ composer require flexmodel/flexmodel
```

## Usage

Load a FlexModel configuration file:

``` php
<?php

use FlexModel\FlexModel;

$domDocument = new DOMDocument('1.0', 'UTF-8');
$domDocument->load('flexmodel.xml');

$flexModel = new FlexModel();
$flexModel->load($domDocument, 'path/to/cache/directory');
```

## The FlexModel XML format

A minimal FlexModel object definition consists of an object name and model definition with fields:

``` xml
<?xml version='1.0' encoding='UTF-8'?>
<flexmodel>
    <object name='TheNameOfTheObject'>
        <fields>
            <field name='name_of_the_field' datatype='VARCHAR'/>
        </fields>
    </object>
</flexmodel>
```

### The object model configuration

The model is defined by one or more field nodes in the fields node of the object, like the example above shows. Each field has a datatype defined and a unique field name.

#### Field datatypes

These are the various datatypes that can be defined for a field:
* BOOLEAN
* DATE
* DATEINTERVAL
* DATETIME
* DECIMAL
* FILE
* FLOAT
* HTML
* INTEGER
* JSON
* TEXT
* SET
* VARCHAR

#### Creating references between objects

References between objects are created by adding a field with an 'OBJECT.ObjectName' datatype.

In the example below you can see an object with the name 'ReferencingObject' with a field refering to an object with the name 'ReferencedObject:
``` xml
<?xml version='1.0' encoding='UTF-8'?>
<flexmodel>
    <object name='ReferencingObject'>
        <fields>
            <field name='referenced_object' datatype='OBJECT.ReferencedObject'/>
        </fields>
    </object>
    <object name='ReferencedObject'>
        <fields>
            <field name='some_other_field' datatype='VARCHAR'/>
        </fields>
    </object>
</flexmodel>
```

## Credits and acknowledgements

- [Niels Nijens][link-author]

Also see the list of [contributors][link-contributors] who participated in this project.


[ico-version]: https://img.shields.io/packagist/v/flexmodel/flexmodel.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-build]: https://travis-ci.org/FlexModel/FlexModel.svg?branch=master
[ico-coverage]: https://coveralls.io/repos/FlexModel/FlexModel/badge.svg?branch=master
[ico-security]: https://img.shields.io/sensiolabs/i/9723eee9-8b0e-4dbf-8bee-baa3e5d2969c.svg

[link-version]: https://packagist.org/packages/flexmodel/flexmodel
[link-build]: https://travis-ci.org/FlexModel/FlexModel
[link-coverage]: https://coveralls.io/r/FlexModel/FlexModel?branch=master
[link-security]: https://insight.sensiolabs.com/projects/9723eee9-8b0e-4dbf-8bee-baa3e5d2969c
[link-author]: https://github.com/niels-nijens
[link-contributors]: https://github.com/FlexModel/FlexModel/contributors
