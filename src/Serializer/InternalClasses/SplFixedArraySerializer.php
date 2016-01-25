<?php

namespace NilPortugues\Serializer\Serializer\InternalClasses;

use NilPortugues\Serializer\Serializer;
use SplFixedArray;

class SplFixedArraySerializer
{
    /**
     * @param Serializer    $serializer
     * @param SplFixedArray $splFixedArray
     *
     * @return array
     */
    public static function serialize(Serializer $serializer, SplFixedArray $splFixedArray)
    {
        $toArray = [
            Serializer::CLASS_IDENTIFIER_KEY => get_class($splFixedArray),
            Serializer::SCALAR_VALUE => [],
        ];
        foreach ($splFixedArray->toArray() as $key => $field) {
            $toArray[Serializer::SCALAR_VALUE][$key] = $serializer->serialize($field);
        }

        return $toArray;
    }

    /**
     * @param Serializer $serializer
     * @param string     $className
     * @param array      $value
     *
     * @return object
     */
    public static function unserialize(Serializer $serializer, $className, array $value)
    {
        $data = $serializer->unserialize($value[Serializer::SCALAR_VALUE]);

        return $className::fromArray($data);
    }
}
