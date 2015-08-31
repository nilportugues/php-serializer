 Serializer for PHP
=========================

[![Build Status](https://travis-ci.org/nilportugues/serializer.svg)](https://travis-ci.org/nilportugues/serializer) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/serializer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nilportugues/serializer/?branch=master)  [![SensioLabsInsight](https://insight.sensiolabs.com/projects/7ae05bba-985d-4359-8a00-3209f85f1d77/mini.png?)](https://insight.sensiolabs.com/projects/7ae05bba-985d-4359-8a00-3209f85f1d77) 
[![Latest Stable Version](https://poser.pugx.org/nilportugues/serializer/v/stable)](https://packagist.org/packages/nilportugues/serializer) 
[![Total Downloads](https://poser.pugx.org/nilportugues/serializer/downloads)](https://packagist.org/packages/nilportugues/serializer) [![License](https://poser.pugx.org/nilportugues/serializer/license)](https://packagist.org/packages/nilportugues/serializer) 


- [Introduction](#introduction)
- [Features](#features)
- [Serialization](#serialization)
  - [Serializers (JSON, XML, YAML)](#serializers-json-xml-yaml)
   - [Example](#example)
   - [Custom Serializers](#custom-serializers)
- [Data Transformation](#data-transformation)
- [Reserved key words](#reserved-key-words) 
- [Quality](#quality)
- [Author](#author)
- [License](#license)


## Introduction 

**What is serialization?**

In the context of data storage, serialization is the process of translating data structures or object state into a format that can be stored (for example, in a file or memory buffer, or transmitted across a network connection link) and reconstructed later in the same or another computer environment.

    
**Why not `serialize()` and `unserialize()`?**

These native functions rely on having the serialized classes loaded and available at runtime and tie your unserialization process to a `PHP` platform.

If the serialized string contains a reference to a class that cannot be instantiated (e.g. class was renamed, moved namespace, removed or changed to abstract) PHP will immediately die with a fatal error.

Is this a problem? Yes it is. Serialized data is now **unusable**.

## Features

- Serialize to JSON, XML and YAML formats.
- Serializes **exact copies** of the object provided:
 - **All object properties**, public, protected and private are serialized.
 - All properties from the current object, and all the inherited properties are read and serialized.
- Handles internal class serialization for objects such as SplFixedArray or classes implementing Traversable.
- Basic **Data Transformers provided** to convert objects to different output formats:
  - ArrayTransformer
  - FlatArrayTransformer
  - JsonTransformer
- **Production-ready**.
- **Extensible:** easily write your out `Serializer` format or `Transformers`.


## Serialization
For the serializer to work, all you need to do is pass in a PHP Object to the serializer and a Strategy to implement its string representation.


### Serializers (JSON, XML, YAML)

- JsonSerializer
- XmlSerializer
- YamlSerializer

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

## Data Transformation

Transformer classes **greatly differ** from a `Strategy` class because these cannot `unserialize()` as all class references are lost in the process of transformation. 

To obtain transformations instead of the `Serializer` class usage of `DeepCopySerializer` is required.

For instance, the library comes with the `JsonTransformer`. Usage is as simple as before, pass to the serializer the new `$strategy`.

```php
use NilPortugues\Serializer\Transformer\Json\JsonTransformer;

//...same as before ...

$strategy = new JsonTransformer();
$serializer = new DeepCopySerializer($strategy);

echo $serializer->serialize($post);
```

`JsonSerializer` output differs from the one provided by the  `JsonTransformer` output is provided in order to compare which one suits your needs best.

**Output with JsonTransformer strategy**

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

## Reserved key words

The parser uses the following reserved key words to store the necessary data to serialize and unserialize objects:

- **@type**: if object property is a class, this key is used to store the class name.
- **@scalar**: for each property, stores the scalar type as a string.
- **@value**: the real property value.
- **@map**: always is `array`.

## Quality

To run the PHPUnit tests at the command line, go to the tests directory and issue phpunit.

This library attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/).

If you notice compliance oversights, please send a patch via pull request.


## Author

Nil Portugués Calderó

 - <contact@nilportugues.com>
 - [http://nilportugues.com](http://nilportugues.com)

## License
The code base is licensed under the MIT license.
