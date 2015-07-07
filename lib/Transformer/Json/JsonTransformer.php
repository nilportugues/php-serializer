<?php

namespace NilPortugues\Api\Transformer\Json;

use NilPortugues\Api\Transformer\AbstractTransformer;

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

        $this->recursiveUnset($value, ['@type']);

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
