<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/3/15
 * Time: 6:00 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Serializer\InternalClasses;

use DateInterval;
use NilPortugues\Serializer\Serializer;
use ReflectionClass;

class DateIntervalSerializer
{
    /**
     * @param Serializer $serializer
     * @param string     $className
     * @param array      $value
     *
     * @return object
     */
    public static function unserialize(Serializer $serializer, $className, array $value)
    {
        $ref = new ReflectionClass($className);

        return self::fillObjectProperties(self::getTypedValue($serializer, $value), $ref);
    }

    /**
     * @param array           $value
     * @param ReflectionClass $ref
     *
     * @return object
     */
    protected static function fillObjectProperties(array $value, ReflectionClass $ref)
    {
        $obj = $ref->newInstanceArgs([$value['construct']]);
        unset($value['construct']);

        foreach ($value as $k => $v) {
            $obj->$k = $v;
        }

        return $obj;
    }

    /**
     * @param Serializer $serializer
     * @param array      $value
     *
     * @return mixed
     */
    protected static function getTypedValue(Serializer $serializer, array $value)
    {
        foreach ($value as &$v) {
            $v = $serializer->unserialize($v);
        }

        return $value;
    }

    /**
     * @param Serializer   $serializer
     * @param DateInterval $dateInterval
     *
     * @return mixed
     */
    public static function serialize(Serializer $serializer, DateInterval $dateInterval)
    {
        return array(
            Serializer::CLASS_IDENTIFIER_KEY => \get_class($dateInterval),
            'construct' => array(
                Serializer::SCALAR_TYPE => 'string',
                Serializer::SCALAR_VALUE => \sprintf(
                    'P%sY%sM%sDT%sH%sM%sS',
                    $dateInterval->y,
                    $dateInterval->m,
                    $dateInterval->d,
                    $dateInterval->h,
                    $dateInterval->i,
                    $dateInterval->s
                ),
            ),
            'invert' => array(
                Serializer::SCALAR_TYPE => 'integer',
                Serializer::SCALAR_VALUE => (empty($dateInterval->invert)) ? 0 : 1,
            ),
            'days' => array(
                Serializer::SCALAR_TYPE => \gettype($dateInterval->days),
                Serializer::SCALAR_VALUE => $dateInterval->days,
            ),
        );
    }
}
