<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/31/15
 * Time: 9:33 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Serializer\Transformer;

use DOMDocument;
use SimpleXMLElement;

/**
 * Class XmlTransformer.
 */
class XmlTransformer extends ArrayTransformer
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $array = parent::serialize($value);

        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');
        $this->arrayToXml($array, $xmlData);
        $xml = $xmlData->asXML();

        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($xml);
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->formatOutput = true;

        return $xmlDoc->saveXML();
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
                    $key = 'sequential-item';
                }
                $subnode = $xmlData->addChild($key);

                $this->arrayToXml($value, $subnode);
            } else {
                $subnode = $xmlData->addChild("$key", "$value");

                $type = \gettype($value);
                if ('array' !== $type) {
                    $subnode->addAttribute('type', $type);
                }
            }
        }
    }
}
