 Serializer for PHP
=========================

[![Build Status](https://travis-ci.org/nilportugues/serializer.svg)](https://travis-ci.org/nilportugues/serializer) [![Coverage Status](https://coveralls.io/repos/nilportugues/serializer/badge.svg?branch=master)](https://coveralls.io/r/nilportugues/serializer?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/serializer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nilportugues/serializer/?branch=master)  [![SensioLabsInsight](https://insight.sensiolabs.com/projects/7ae05bba-985d-4359-8a00-3209f85f1d77/mini.png)](https://insight.sensiolabs.com/projects/7ae05bba-985d-4359-8a00-3209f85f1d77) [![Latest Stable Version](https://poser.pugx.org/nilportugues/serializer/v/stable)](https://packagist.org/packages/nilportugues/serializer) [![Total Downloads](https://poser.pugx.org/nilportugues/serializer/downloads)](https://packagist.org/packages/nilportugues/serializer) [![License](https://poser.pugx.org/nilportugues/serializer/license)](https://packagist.org/packages/nilportugues/serializer) 


#### What is serialization? 

```
In the context of data storage, serialization is the process of translating data structures 
or object state into a   format that can be stored (for example, in a file or memory buffer, 
or transmitted across a network connection link) and reconstructed later in the same or 
another computer environment.
```
    
#### Why not `serialize()` and `unserialize()`?

These native functions rely on having the serialized classes loaded and available at runtime and tie your unserialization process to a `PHP` platform.

If the serialized string contains a reference to a class that cannot be instantiated (e.g. class was renamed, moved namespace, removed or changed to abstract) PHP will immediately die with a fatal error.

Is this a problem? Yes it is. Serialized data is now **unusable**.


<a name="block4"></a>
# Quality Code [↑](#index_block)
Testing has been done using PHPUnit and [Travis-CI](https://travis-ci.org). All code has been tested to be compatible from PHP 5.5 up to PHP 7 and [HHVM](http://hhvm.com/).

To run the test suite, you need [Composer](http://getcomposer.org):

```bash
    php composer.phar install --dev
    php bin/phpunit
```

<a name="block5"></a>
# Author [↑](#index_block)
Nil Portugués Calderó

 - <contact@nilportugues.com>
 - [http://nilportugues.com](http://nilportugues.com)


<a name="block6"></a>
#  License [↑](#index_block)
Code is licensed under the GPLv3 license.
