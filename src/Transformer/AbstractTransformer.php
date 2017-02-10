<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/17/15
 * Time: 11:40 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Transformer;

use InvalidArgumentException;
use NilPortugues\Serializer\Serializer;
use NilPortugues\Serializer\Strategy\StrategyInterface;

abstract class AbstractTransformer implements StrategyInterface
{
    /**
     * @param array $array
     * @param array $unwantedKey
     */
    protected function recursiveUnset(array &$array, array $unwantedKey)
    {
        foreach ($unwantedKey as $key) {
            if (\array_key_exists($key, $array)) {
                unset($array[$key]);
            }
        }

        foreach ($array as &$value) {
            if (\is_array($value)) {
                $this->recursiveUnset($value, $unwantedKey);
            }
        }
    }

    /**
     * @param array $array
     */
    protected function recursiveSetValues(array &$array)
    {
        if (\array_key_exists(Serializer::SCALAR_VALUE, $array)) {
            $array = $array[Serializer::SCALAR_VALUE];
        }

        if (\is_array($array) && !array_key_exists(Serializer::SCALAR_VALUE, $array)) {
            foreach ($array as &$value) {
                if (\is_array($value)) {
                    $this->recursiveSetValues($value);
                }
            }
        }
    }

    /**
     * @param array $array
     * @param null $parentKey
     * @param null $currentKey
     */
    protected function recursiveFlattenOneElementObjectsToScalarType(array &$array, $parentKey = null, $currentKey = null)
    {
        if (1 === \count($array) && \is_scalar(\end($array))) {
            if ($parentKey == $currentKey) {
                $array = \array_pop($array);
            }
        }

        if (\is_array($array)) {
            foreach ($array as $parentKey => &$value) {

                if (\is_array($value)) {
                    $key = null;
                    foreach($value as $key => $v) {
                        if (is_array($v)) {
                            $this->recursiveFlattenOneElementObjectsToScalarType($v, $parentKey, $key);
                        }
                    }
                    $this->recursiveFlattenOneElementObjectsToScalarType($value, $parentKey, $key);
                }
            }
        }
    }

    /**
     * @param $value
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function unserialize($value)
    {
        throw new InvalidArgumentException(\sprintf('%s does not perform unserializations.', __CLASS__));
    }
}
