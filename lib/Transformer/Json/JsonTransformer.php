<?php

namespace NilPortugues\Api\Transformer\Json;

use NilPortugues\Api\Transformer\AbstractTransformer;
use NilPortugues\Serializer\Serializer;

/**
 * Class JsonTransformer.
 */
class JsonTransformer extends AbstractTransformer
{
    public function __construct()
    {
        //overwriting default constructor.
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {

        $this->recursiveSetValues($value);
        $this->recursiveUnset($value, [Serializer::CLASS_IDENTIFIER_KEY]);
        $this->recursiveFlattenOneElementObjectsToScalarType($value);



        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array $array
     */
    private function recursiveFlattenOneElementObjectsToScalarType(array &$array)
    {

        if (1 === count($array) && is_scalar(end($array))) {
            $array = array_pop($array);
        }

        if (is_array($array)) {
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $this->recursiveFlattenOneElementObjectsToScalarType($value);
                }
            }
        }
    }
}
