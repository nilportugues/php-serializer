<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/3/15
 * Time: 6:16 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Serializer\UserDefined;

/**
 * Class ScalarSerializer.
 */
class ScalarSerializer
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function serialize($value)
    {
        return $value;
    }
}
