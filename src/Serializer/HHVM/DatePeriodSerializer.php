<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/4/15
 * Time: 12:41 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Serializer\HHVM;

/**
 * Class DatePeriodSerializer.
 */
class DatePeriodSerializer
{
    //HHVM's implementation is completely different
    /*
 [start:DatePeriod:private] => DateTime Object
        (
            [date] => 2012-07-01 00:00:00.000000
            [timezone_type] => 3
            [timezone] => UTC
        )

    [interval:DatePeriod:private] => DateInterval Object
        (
        )

    [end:DatePeriod:private] => DateTime Object
        (
            [date] => 2012-08-05 00:00:00.000000
            [timezone_type] => 3
            [timezone] => UTC
        )

    [options:DatePeriod:private] =>
    [current:DatePeriod:private] => DateTime Object
        (
            [date] => 2012-08-05 00:00:00.000000
            [timezone_type] => 3
            [timezone] => UTC
        )

    [recurrances:DatePeriod:private] => 4
    [iterKey:DatePeriod:private] => 5

     */
}
