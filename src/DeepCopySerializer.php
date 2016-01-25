<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/28/15
 * Time: 1:44 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer;

use ReflectionClass;

class DeepCopySerializer extends Serializer
{
    /**
     * Extract the data from an object.
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function serializeObject($value)
    {
        if (self::$objectStorage->contains($value)) {
            return self::$objectStorage[$value];
        }

        $reflection = new ReflectionClass($value);
        $className = $reflection->getName();

        $serialized = $this->serializeInternalClass($value, $className, $reflection);
        self::$objectStorage->attach($value, $serialized);

        return $serialized;
    }
}
