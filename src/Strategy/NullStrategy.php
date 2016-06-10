<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/29/15
 * Time: 12:44 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Strategy;

/**
 * Class NullStrategy.
 */
class NullStrategy implements StrategyInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * @param string $value
     *
     * @return array
     */
    public function unserialize($value)
    {
        return $value;
    }
}
