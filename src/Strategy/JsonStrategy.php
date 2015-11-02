<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/3/15
 * Time: 6:11 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Strategy;

/**
 * Class JsonStrategy.
 */
class JsonStrategy implements StrategyInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return \json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function unserialize($value)
    {
        return \json_decode($value, true);
    }
}
