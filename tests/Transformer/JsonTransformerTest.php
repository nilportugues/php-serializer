<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/30/15
 * Time: 1:03 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Test\Serializer\Transformer;

use DateTime;
use NilPortugues\Serializer\DeepCopySerializer;
use NilPortugues\Serializer\Transformer\JsonTransformer;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\Comment;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\Post;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\User;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\ValueObject\CommentId;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\ValueObject\PostId;
use NilPortugues\Test\Serializer\Dummy\ComplexObject\ValueObject\UserId;

class JsonTransformerTest extends \PHPUnit_Framework_TestCase
{

    public function testSerializationObjectWithArrayOfOneAttribute()
    {
        $object = new \stdClass();
        $object->payload = ['userId' => 1];

        $serializer = new DeepCopySerializer(new JsonTransformer());

        $expected = <<<STRING
{
    "payload": {
        "userId": 1
    }
}
STRING;

        $this->assertEquals($expected, $serializer->serialize($object));
    }

    public function testSerializationObjectWithObjectOfOneAttribute()
    {
        $internal = new \stdClass();
        $internal->userId = 1;

        $object = new \stdClass();
        $object->payload = $internal;

        $serializer = new DeepCopySerializer(new JsonTransformer());

        $expected = <<<STRING
{
    "payload": {
        "userId": 1
    }
}
STRING;

        $this->assertEquals($expected, $serializer->serialize($object));
    }

    public function testSerialization()
    {
        $object = $this->getObject();
        $serializer = new DeepCopySerializer(new JsonTransformer());

        $expected = <<<STRING
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
                "created_at": "2015-07-18T12:13:00+02:00",
                "accepted_at": "2015-07-19T00:00:00+02:00"
            },
            "comment": "Have no fear, sers, your king is safe.",
            "user": {
                "userId": 2,
                "name": "Barristan Selmy"
            }
        }
    ]
}
STRING;

        $this->assertEquals($expected, $serializer->serialize($object));
    }

    /**
     * @return Post
     */
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
        $serializer = new DeepCopySerializer(new JsonTransformer());

        $expected = <<<STRING
[
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
                    "created_at": "2015-07-18T12:13:00+02:00",
                    "accepted_at": "2015-07-19T00:00:00+02:00"
                },
                "comment": "Have no fear, sers, your king is safe.",
                "user": {
                    "userId": 2,
                    "name": "Barristan Selmy"
                }
            }
        ]
    },
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
                    "created_at": "2015-07-18T12:13:00+02:00",
                    "accepted_at": "2015-07-19T00:00:00+02:00"
                },
                "comment": "Have no fear, sers, your king is safe.",
                "user": {
                    "userId": 2,
                    "name": "Barristan Selmy"
                }
            }
        ]
    }
]
STRING;

        $this->assertEquals($expected, $serializer->serialize($arrayOfObjects));
    }

    public function testUnserializeWillThrowException()
    {
        $serialize = new DeepCopySerializer(new JsonTransformer());

        $this->setExpectedException(\InvalidArgumentException::class);
        $serialize->unserialize($serialize->serialize($this->getObject()));
    }
}
