<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/31/15
 * Time: 9:11 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Transformer;

use Symfony\Component\Yaml\Yaml;

class YamlTransformer extends ArrayTransformer
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return Yaml::dump(parent::serialize($value));
    }
}
