<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/3/15
 * Time: 6:20 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Serializer\Serializer;

use NilPortugues\Serializer\Serializer;

/**
 * Class SerializerProvider.
 */
class SerializerProvider
{
    /**
     * @var array
     */
    private $providers = array();

    /**
     * @var \NilPortugues\Serializer\Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string                     $alias
     * @param AbstractSerializerProvider $provider
     */
    public function addProvider($alias, AbstractSerializerProvider $provider)
    {
        $this->providers = array_merge(
            $this->providers,
            array($alias => $provider->getSerializers())
        );
    }

    /**
     * @param string $type
     * @param bool   $isInternal
     * @param bool   $isHHVM
     */
    public function get($type, $isInternal, $isHHVM = false)
    {
    }
}
