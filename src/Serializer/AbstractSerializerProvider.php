<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/3/15
 * Time: 6:21 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Serializer\Serializer;

/**
 * Class AbstractSerializerProvider.
 */
abstract class AbstractSerializerProvider
{
    /**
     * @var array
     */
    protected $serializers = array();

    /**
     * @return array
     */
    public function getSerializers()
    {
        return $this->serializers;
    }
}
