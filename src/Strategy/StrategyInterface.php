<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/4/15
 * Time: 2:56 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Strategy;

interface StrategyInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value);

    /**
     * @param $value
     *
     * @return array
     */
    public function unserialize($value);
}
