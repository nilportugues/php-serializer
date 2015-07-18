 Serializer for PHP
=========================

[![Build Status](https://travis-ci.org/nilportugues/serializer.svg)](https://travis-ci.org/nilportugues/serializer) [![Coverage Status](https://coveralls.io/repos/nilportugues/serializer/badge.svg?branch=master)](https://coveralls.io/r/nilportugues/serializer?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/serializer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nilportugues/serializer/?branch=master)  [![SensioLabsInsight](https://insight.sensiolabs.com/projects/7ae05bba-985d-4359-8a00-3209f85f1d77/mini.png)](https://insight.sensiolabs.com/projects/7ae05bba-985d-4359-8a00-3209f85f1d77) [![Latest Stable Version](https://poser.pugx.org/nilportugues/serializer/v/stable)](https://packagist.org/packages/nilportugues/serializer) [![Total Downloads](https://poser.pugx.org/nilportugues/serializer/downloads)](https://packagist.org/packages/nilportugues/serializer) [![License](https://poser.pugx.org/nilportugues/serializer/license)](https://packagist.org/packages/nilportugues/serializer) 

# Introduction 

** What is serialization? **
```
In the context of data storage, serialization is the process of translating data structures 
or object state into a   format that can be stored (for example, in a file or memory buffer, 
or transmitted across a network connection link) and reconstructed later in the same or 
another computer environment.
```
    
** Why not `serialize()` and `unserialize()`?**

These native functions rely on having the serialized classes loaded and available at runtime and tie your unserialization process to a `PHP` platform.

If the serialized string contains a reference to a class that cannot be instantiated (e.g. class was renamed, moved namespace, removed or changed to abstract) PHP will immediately die with a fatal error.

Is this a problem? Yes it is. Serialized data is now **unusable**.

# Features

- All object variables, public, protected and private are serialized. 
- Handles internal class serialization for objects such as SplFixedArray or classes implementing Traversable.


# Usage
For the serializer to work, all you need to do is pass in a PHP Object to the serializer, that will require of a represetation (aka Transformer). 

In the following example a `$post` object is serialized into JSON. 

**Code**

```php
use NilPortugues\Serializer\Serializer;
use NilPortugues\Serializer\Transformer\Json\JsonTransformer;

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
$transformer = new JsonTransformer();
$serializer = new Serializer($transformer);

echo $serializer->serialize($post);
```

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


# Quality Code [↑](#index_block)
Testing has been done using PHPUnit and [Travis-CI](https://travis-ci.org). All code has been tested to be compatible from PHP 5.5 up to PHP 7 and [HHVM](http://hhvm.com/).

To run the test suite, you need [Composer](http://getcomposer.org):

```bash
    php composer.phar install --dev
    php bin/phpunit
```

# Author
Nil Portugués Calderó

 - <contact@nilportugues.com>
 - [http://nilportugues.com](http://nilportugues.com)


<a name="block6"></a>
#  License [↑](#index_block)
Code is licensed under the GPLv3 license.
