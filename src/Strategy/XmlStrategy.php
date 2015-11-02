<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/29/15
 * Time: 12:46 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Strategy;

use NilPortugues\Serializer\Serializer;
use SimpleXMLElement;

class XmlStrategy implements StrategyInterface
{
    /**
     * @var array
     */
    private $replacements = [
        Serializer::CLASS_IDENTIFIER_KEY => 'np_serializer_type',
        Serializer::SCALAR_TYPE => 'np_serializer_scalar',
        Serializer::SCALAR_VALUE => 'np_serializer_value',
        Serializer::MAP_TYPE => 'np_serializer_map',
    ];

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $value = self::replaceKeys($this->replacements, $value);
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');
        $this->arrayToXml($value, $xml);

        return $xml->asXML();
    }

    /**
     * @param array $replacements
     * @param array $input
     *
     * @return array
     */
    private static function replaceKeys(array &$replacements, array $input)
    {
        $return = [];
        foreach ($input as $key => $value) {
            $key = \str_replace(\array_keys($replacements), \array_values($replacements), $key);

            if (\is_array($value)) {
                $value = self::replaceKeys($replacements, $value);
            }

            $return[$key] = $value;
        }

        return $return;
    }

    /**
     * Converts an array to XML using SimpleXMLElement.
     *
     * @param array            $data
     * @param SimpleXMLElement $xmlData
     */
    private function arrayToXml(array &$data, SimpleXMLElement $xmlData)
    {
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                if (\is_numeric($key)) {
                    $key = 'np_serializer_element_'.gettype($key).'_'.$key;
                }
                $subnode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xmlData->addChild("$key", "$value");
            }
        }
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function unserialize($value)
    {
        $array = (array) \simplexml_load_string($value);
        self::castToArray($array);
        self::recoverArrayNumericKeyValues($array);
        $replacements = \array_flip($this->replacements);
        $array = self::replaceKeys($replacements, $array);

        return $array;
    }

    /**
     * @param array $array
     */
    private static function castToArray(array &$array)
    {
        foreach ($array as &$value) {
            if ($value instanceof SimpleXMLElement) {
                $value = (array) $value;
            }

            if (\is_array($value)) {
                self::castToArray($value);
            }
        }
    }

    /**
     * @param array $array
     */
    private static function recoverArrayNumericKeyValues(array &$array)
    {
        $newArray = [];
        foreach ($array as $key => &$value) {
            if (false !== \strpos($key, 'np_serializer_element_')) {
                $key = self::getNumericKeyValue($key);
            }

            $newArray[$key] = $value;

            if (\is_array($newArray[$key])) {
                self::recoverArrayNumericKeyValues($newArray[$key]);
            }
        }
        $array = $newArray;
    }

    /**
     * @param $key
     *
     * @return float|int
     */
    private static function getNumericKeyValue($key)
    {
        $newKey = \str_replace('np_serializer_element_', '', $key);
        list($type, $index) = \explode('_', $newKey);

        if ('integer' === $type) {
            $index = (int) $index;
        }

        return $index;
    }
}
