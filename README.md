Serializer for PHP
=========================

[![Build Status](https://travis-ci.org/nilportugues/php-serializer.svg)](https://travis-ci.org/nilportugues/php-serializer)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/serializer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nilportugues/serializer/?branch=master)  [![SensioLabsInsight](https://insight.sensiolabs.com/projects/7ae05bba-985d-4359-8a00-3209f85f1d77/mini.png?)](https://insight.sensiolabs.com/projects/7ae05bba-985d-4359-8a00-3209f85f1d77) 
[![Latest Stable Version](https://poser.pugx.org/nilportugues/serializer/v/stable)](https://packagist.org/packages/nilportugues/serializer) 
[![Total Downloads](https://poser.pugx.org/nilportugues/serializer/downloads)](https://packagist.org/packages/nilportugues/serializer) [![License](https://poser.pugx.org/nilportugues/serializer/license)](https://packagist.org/packages/nilportugues/serializer) 
[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif)](https://paypal.me/nilportugues)

- [Installation](#installation)
- [Introduction](#introduction)
- [Features](#features)
- [Serialization](#serialization)
  - [Serializers (JSON, XML, YAML)](#serializers-json-xml-yaml)
   - [Example](#example)
   - [Custom Serializers](#custom-serializers)
- [Data Transformation](#data-transformation)
   - [Array Transformer](#array-transformer)
   - [Flat Array Transformer](#flat-array-transformer) 
   - [XML Transformer](#xml-transformer) 
   - [YAML Transformer](#yaml-transformer) 
   - [JSON Transformer](#json-transformer)
   - [JSend Transformer](#jsend-transformer)
   - [JSON API Transformer](#json-api-transformer)
   - [HAL+JSON Transformer](#haljson-transformer)
- [Quality](#quality)
- [Contribute](#contribute)
- [Author](#author)
- [License](#license)

## Installation

Use [Composer](https://getcomposer.org) to install the package:

```json
$ composer require nilportugues/serializer
```

## Introduction 

**What is serialization?**

In the context of data storage, serialization is the process of translating data structures or object state into a format that can be stored (for example, in a file or memory buffer, or transmitted across a network connection link) and reconstructed later in the same or another computer environment.

    
**Why not `serialize()` and `unserialize()`?**

These native functions rely on having the serialized classes loaded and available at runtime and tie your unserialization process to a `PHP` platform.

If the serialized string contains a reference to a class that cannot be instantiated (e.g. class was renamed, moved namespace, removed or changed to abstract) PHP will immediately die with a fatal error.

Is this a problem? Yes it is. Serialized data is now **unusable**.

## Features

- Serialize to **JSON**, **XML** and **YAML** formats.
- Serializes **exact copies** of the object provided:
 - **All object properties**, public, protected and private are serialized.
 - All properties from the current object, and all the inherited properties are read and serialized.
- Handles internal class serialization for objects such as SplFixedArray or classes implementing Traversable.
- Basic **Data Transformers provided** to convert objects to different output formats.
- **Production-ready**.
- **Extensible:** easily write your out `Serializer` format or data `Transformers`.


## Serialization
For the serializer to work, all you need to do is pass in a PHP Object to the serializer and a Strategy to implement its string representation.


### Serializers (JSON, XML, YAML)

- [NilPortugues\Serializer\JsonSerializer](https://github.com/nilportugues/serializer/blob/master/src/JsonSerializer.php)
- [NilPortugues\Serializer\XmlSerializer](https://github.com/nilportugues/serializer/blob/master/src/XmlSerializer.php)
- [NilPortugues\Serializer\YamlSerializer](https://github.com/nilportugues/serializer/blob/master/src/YamlSerializer.php)

### Example

In the following example a `$post` object is serialized into JSON. 

**Code**

```php
use NilPortugues\Serializer\Serializer;
use NilPortugues\Serializer\Strategy\JsonStrategy;

//Example object
$post = new Post(
  new PostId(9),
  'Hello World',
  'Your first post',
  new User(
      new UserId(1),
      'Post Author'
  ),
  [
      new Comment(
          new CommentId(1000),
          'Have no fear, sers, your king is safe.',
          new User(new UserId(2), 'Barristan Selmy'),
          [
              'created_at' => (new DateTime('2015/07/18 12:13:00'))->format('c'),
              'accepted_at' => (new DateTime('2015/07/19 00:00:00'))->format('c'),
          ]
      ),
  ]
);

//Serialization 
$serializer = new JsonSerializer();

$serializedObject = $serializer->serialize($post);

//Returns: true
var_dump($post == $serializer->unserialize($serializedObject));

echo $serializedObject;
```

The object, before it's transformed into an output format, is an array with all the necessary data to be rebuild using unserialize method. 

**Output**

```json
{
    "@type": "Acme\\\\Domain\\\\Dummy\\\\Post",
    "postId": {
        "@type": "Acme\\\\Domain\\\\Dummy\\\\ValueObject\\\\PostId",
        "postId": {
            "@scalar": "integer",
            "@value": 14
        }
    },
    "title": {
        "@scalar": "string",
        "@value": "Hello World"
    },
    "content": {
        "@scalar": "string",
        "@value": "Your first post"
    },
    "author": {
        "@type": "Acme\\\\Domain\\\\Dummy\\\\User",
        "userId": {
            "@type": "Acme\\\\Domain\\\\Dummy\\\\ValueObject\\\\UserId",
            "userId": {
                "@scalar": "integer",
                "@value": 1
            }
        },
        "name": {
            "@scalar": "string",
            "@value": "Post Author"
        }
    },
    "comments": {
        "@map": "array",
        "@value": [
            {
                "@type": "Acme\\\\Domain\\\\Dummy\\\\Comment",
                "commentId": {
                    "@type": "Acme\\\\Domain\\\\Dummy\\\\ValueObject\\\\CommentId",
                    "commentId": {
                        "@scalar": "integer",
                        "@value": 1000
                    }
                },
                "dates": {
                    "@map": "array",
                    "@value": {
                        "created_at": {
                            "@scalar": "string",
                            "@value": "2015-07-18T12:13:00+00:00"
                        },
                        "accepted_at": {
                            "@scalar": "string",
                            "@value": "2015-07-19T00:00:00+00:00"
                        }
                    }
                },
                "comment": {
                    "@scalar": "string",
                    "@value": "Have no fear, sers, your king is safe."
                },
                "user": {
                    "@type": "Acme\\\\Domain\\\\Dummy\\\\User",
                    "userId": {
                        "@type": "Acme\\\\Domain\\\\Dummy\\\\ValueObject\\\\UserId",
                        "userId": {
                            "@scalar": "integer",
                            "@value": 2
                        }
                    },
                    "name": {
                        "@scalar": "string",
                        "@value": "Barristan Selmy"
                    }
                }
            }
        ]
    }
}'
```

### Custom Serializers

If a custom serialization strategy is preferred, the `Serializer` class should be used instead. A `CustomStrategy` must implement the `StrategyInterface`.

Usage is as follows:

```php
use NilPortugues\Serializer\Serializer;
use NilPortugues\Serializer\Strategy\CustomStrategy;

$serializer = new Serializer(new CustomStrategy());

echo $serializer->serialize($post);
```

----


## Data Transformation

Transformer classes **greatly differ** from a `Strategy` class because these cannot `unserialize()` as all class references are lost in the process of transformation. 

To obtain transformations instead of the `Serializer` class usage of `DeepCopySerializer` is required.

The Serializer library comes with a set of defined Transformers that implement the `StrategyInterface`. 
Usage is as simple as before, pass a Transformer as a `$strategy`. 

**For instance:**

```php
//...same as before ...

$serializer = new DeepCopySerializer(new JsonTransformer());
echo $serializer->serialize($post);
```

Following, there are some examples and its output, given the `$post` object as data to be Transformed.

### Array Transformer

- [`NilPortugues\Serializer\Transformer\ArrayTransformer`](https://github.com/nilportugues/serializer/blob/master/src/Transformer/ArrayTransformer.php)


```php
array(
  'postId' => 9,
  'title' => 'Hello World',
  'content' => 'Your first post',
  'author' => array(
       'userId' => 1,
       'name' => 'Post Author',
   ),
  'comments' => array(
          0 => array(
           'commentId' => 1000,
           'dates' => array(
              'created_at' => '2015-07-18T12:13:00+02:00',
              'accepted_at' => '2015-07-19T00:00:00+02:00',
            ),
           'comment' => 'Have no fear, sers, your king is safe.',
           'user' => array(
             'userId' => 2,
             'name' => 'Barristan Selmy',
            ),
          ),
      ),
);
```

### Flat Array Transformer

- [`NilPortugues\Serializer\Transformer\FlatArrayTransformer`](https://github.com/nilportugues/serializer/blob/master/src/Transformer/FlatArrayTransformer.php)

```php
array(
  'postId' => 9,
  'title' => 'Hello World',
  'content' => 'Your first post',
  'author.userId' => 1,
  'author.name' => 'Post Author',
  'comments.0.commentId' => 1000,
  'comments.0.dates.created_at' => '2015-07-18T12:13:00+02:00',
  'comments.0.dates.accepted_at' => '2015-07-19T00:00:00+02:00',
  'comments.0.comment' => 'Have no fear, sers, your king is safe.',
  'comments.0.user.userId' => 2,
  'comments.0.user.name' => 'Barristan Selmy',
);
```

### XML Transformer

- [`NilPortugues\Serializer\Transformer\XmlTransformer`](https://github.com/nilportugues/serializer/blob/master/src/Transformer/XmlTransformer.php)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<data>
  <postId type="integer">9</postId>
  <title type="string">Hello World</title>
  <content type="string">Your first post</content>
  <author>
    <userId type="integer">1</userId>
    <name type="string">Post Author</name>
  </author>
  <comments>
    <sequential-item>
      <commentId type="integer">1000</commentId>
      <dates>
        <created_at type="string">2015-07-18T12:13:00+02:00</created_at>
        <accepted_at type="string">2015-07-19T00:00:00+02:00</accepted_at>
      </dates>
      <comment type="string">Have no fear, sers, your king is safe.</comment>
      <user>
        <userId type="integer">2</userId>
        <name type="string">Barristan Selmy</name>
      </user>
    </sequential-item>
  </comments>
</data>
```

### YAML Transformer

- [`NilPortugues\Serializer\Transformer\YamlTransformer`](https://github.com/nilportugues/serializer/blob/master/src/Transformer/YamlTransformer.php)

```yml
title: 'Hello World'
content: 'Your first post'
author:
    userId: 1
    name: 'Post Author'
comments:
    - { commentId: 1000, dates: { created_at: '2015-07-18T12:13:00+02:00', accepted_at: '2015-07-19T00:00:00+02:00' }, comment: 'Have no fear, sers, your king is safe.', user: { userId: 2, name: 'Barristan Selmy' } }
```


### Json Transformer

JsonTransformer comes in 2 flavours. For object to JSON transformation the following transformer should be used:

- [`NilPortugues\Serializer\Transformer\JsonTransformer`](https://github.com/nilportugues/serializer/blob/master/src/Transformer/JsonTransformer.php)

**Output**

```json
{
    "postId": 9,
    "title": "Hello World",
    "content": "Your first post",
    "author": {
        "userId": 1,
        "name": "Post Author"
    },
    "comments": [
        {
            "commentId": 1000,
            "dates": {
                "created_at": "2015-07-18T13:34:55+02:00",
                "accepted_at": "2015-07-18T14:09:55+02:00"
            },
            "comment": "Have no fear, sers, your king is safe.",
            "user": {
                "userId": 2,
                "name": "Barristan Selmy"
            }
        }
    ]
}
``` 

If your desired output is for **API consumption**, you may like to check out the JsonTransformer library, or require it using:

```json
$ composer require nilportugues/json
```


### JSend Transformer

JSend Transformer has been built to transform data into valid **JSend** specification resources.

Please check out the [JSend Transformer](https://github.com/nilportugues/jsend-transformer) or download it using:

```json
$ composer require nilportugues/jsend
```


### JSON API Transformer

JSON API Transformer has been built to transform data into valid **JSON API** specification resources.

Please check out the [JSON API Transformer](https://github.com/nilportugues/jsonapi-transformer) or download it using:

```json
$ composer require nilportugues/json-api
```


### HAL+JSON Transformer

HAL+JSON Transformer has been built for **HAL+JSON API creation**. Given an object and a series of mappings a valid HAL+JSON resource representation is given as output.

Please check out the [HAL+JSON API Transformer](https://github.com/nilportugues/hal-json-transformer) or download it using:

```json
$ composer require nilportugues/haljson
```

----


## Quality

To run the PHPUnit tests at the command line, go to the tests directory and issue `phpunit`.

This library attempts to comply with [PSR-2](http://www.php-fig.org/psr/psr-2/) and [PSR-4](http://www.php-fig.org/psr/psr-4/).

If you notice compliance oversights, please send a patch via pull request.

## Contribute

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker](https://github.com/nilportugues/serializer/issues/new).
* You can grab the source code at the package's [Git repository](https://github.com/nilportugues/serializer).

## Authors

* [Nil Portugués Calderó](http://nilportugues.com)
* [The Community Contributors](https://github.com/nilportugues/serializer/graphs/contributors)

## License
The code base is licensed under the MIT license.
