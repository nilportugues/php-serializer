<?php

namespace NilPortugues\Serializer\Transformer;

/**
 * Class JsonTransformer.
 */
class JsonTransformer extends ArrayTransformer
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return \json_encode(
            parent::serialize($value),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
