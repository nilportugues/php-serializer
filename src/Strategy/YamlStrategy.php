<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/27/15
 * Time: 11:58 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Strategy;

use Symfony\Component\Yaml\Yaml;

class YamlStrategy implements StrategyInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return Yaml::dump($value);
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function unserialize($value)
    {
        return Yaml::parse($value);
    }
}
