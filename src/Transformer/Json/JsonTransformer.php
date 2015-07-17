<?php

namespace NilPortugues\Serializer\Transformer\Json;

use NilPortugues\Serializer\Serializer;
use NilPortugues\Serializer\Transformer\AbstractTransformer;

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
}
