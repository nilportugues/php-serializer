<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/3/15
 * Time: 6:19 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Serializer;

/**
 * Class SerializerProvider.
 */
class HHVMSerializerProvider extends AbstractSerializerProvider
{
    /**
     * @var array
     */
    protected $serializers = array(
        'DateInterval' => '\NilPortugues\Serializer\Serializer\HHVM\DateIntervalSerializer',
        'DateTimeImmutable' => '\NilPortugues\Serializer\Serializer\HHVM\DateTimeImmutableSerializer',
        'DateTimeZone' => '\NilPortugues\Serializer\Serializer\HHVM\DateTimeZoneSerializer',
    );
}
