<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/30/15
 * Time: 12:33 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Test\Serializer;

use DateTime;
use NilPortugues\Serializer\DeepCopySerializer;
use NilPortugues\Serializer\Strategy\NullStrategy;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\Comment;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\Post;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\User;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\ValueObject\CommentId;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\ValueObject\PostId;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\ValueObject\UserId;
use NilPortugues\Test\Serializer\SupportClasses\ChildOfSplFixedArray;
use SplFixedArray;

class DeepCopySerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testSerialization()
    {
        $object = $this->getObject();
        $serializer = new DeepCopySerializer(new NullStrategy());
        $serializedObject = $serializer->serialize($object);

        $this->assertEquals($object, $serializer->unserialize($serializedObject));
    }

    private function getObject()
    {
        return new Post(
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
    }

    public function testArraySerialization()
    {
        $arrayOfObjects = [$this->getObject(), $this->getObject()];
        $serializer = new DeepCopySerializer(new NullStrategy());
        $serializedObject = $serializer->serialize($arrayOfObjects);

        $this->assertEquals($arrayOfObjects, $serializer->unserialize($serializedObject));
    }

    public function testObjectStorageCopyDuringSerialization()
    {
        $post = $this->getObject();

        $stdClass = new \stdClass();
        $stdClass->first = $post;
        $stdClass->second = $post;

        $serializer = new DeepCopySerializer(new NullStrategy());
        $serializedObject = $serializer->serialize($stdClass);

        $this->assertEquals($stdClass, $serializer->unserialize($serializedObject));
    }

    public function testSplFixedArraySerialization()
    {
        $splFixedArray = new SplFixedArray(3);
        $splFixedArray[0] = 1;
        $splFixedArray[1] = 2;
        $splFixedArray[2] = 3;

        $serializer = new DeepCopySerializer(new NullStrategy());
        $serializedObject = $serializer->serialize($splFixedArray);

        $this->assertEquals($splFixedArray, $serializer->unserialize($serializedObject));
    }

    public function testSplFixedArrayChildSerialization()
    {
        $splFixedArray = new ChildOfSplFixedArray(3);
        $splFixedArray[0] = 1;
        $splFixedArray[1] = 2;
        $splFixedArray[2] = 3;

        $serializer = new DeepCopySerializer(new NullStrategy());
        $serializedObject = $serializer->serialize($splFixedArray);

        $this->assertEquals($splFixedArray, $serializer->unserialize($serializedObject));
    }
}
