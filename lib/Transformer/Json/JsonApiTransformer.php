<?php

namespace NilPortugues\Api\Transformer\Json;

use NilPortugues\Api\Transformer\AbstractTransformer;
use NilPortugues\Serializer\Serializer;

/**
 * This Transformer follows the http://jsonapi.org specification.
 *
 * @link http://jsonapi.org/format/#document-structure
 */
class JsonApiTransformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $meta = [];
    /**
     * @var string
     */
    private $apiVersion = '';
    /**
     * @var array
     */
    private $relationships = [];
    /**
     * @var array
     */
    private $included = [];
    /**
     * @var string
     */
    private $relatedUrl = '';
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     *
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelatedUrl()
    {
        return $this->relatedUrl;
    }

    /**
     * @param string $relatedUrl
     *
     * @return $this
     */
    public function setRelatedUrl($relatedUrl)
    {
        $this->relatedUrl = $relatedUrl;

        return $this;
    }

    /**
     * @return array
     */
    public function getIncluded()
    {
        return $this->included;
    }

    /**
     * @param array $included
     *
     * @return $this
     */
    public function setIncluded($included)
    {
        $this->included = $included;

        return $this;
    }

    /**
     * @return array
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * @param array $relationships
     *
     * @return $this
     */
    public function setRelationships($relationships)
    {
        $this->relationships = $relationships;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     *
     * @return $this
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     *
     * @return $this
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $included = [];
        $this->recursiveSetValues($value);
        $this->recursiveSetApiDataStructure($value, $included);

        //@todo: Implmenent methods
        foreach ($this->mappings as $mapping) {
            //$this->recursiveUnsetClassKey($value, $mapping->getHiddenProperties(), $mapping->getClassName());
            //$this->recursiveRenameKeys($value, $mapping->getAliasedProperties(), $mapping->getClassName());
        }

        $this->recursiveUnset($value, ['@type']);

        return json_encode(
            $this->buildResponse($value, $included),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param array $array
     * @param array $included
     */
    private function recursiveSetApiDataStructure(array &$array, array &$included)
    {
        if (is_array($array)) {
            $id = [];
            $type = null;
            $attributes = [];
            $meta = [];
            $relationships = [];
            $links = [];

            foreach ($array as $key => $value) {
                if ($key === Serializer::CLASS_IDENTIFIER_KEY) {
                    $type = $this->namespaceAsArrayKey($value);

                } elseif ($this->isIdentifierKey($array, $key)) {

                    $id = $this->setIdentifierKey($value, $id);

                    $meta = $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getMetaData();


                    $resourceUrl = $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getResourceUrl();
                    if(!empty($resourceUrl)) {
                        $replacementKeys = $this->getUrlReplacementKeys($array);
                        $replacementValues =  $this->getUrlReplacementValues($array);
                        $links['self'] = str_replace($replacementKeys,$replacementValues, $resourceUrl);

                    }

                    $title = $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getResourceUrlTitlePattern();
                    if(!empty($title)) {
                        $links['title'] = $this->setUrlTitle($title, $array);
                    }
                }
                else {
                    unset($array[$key]);
                    if (is_array($value) ) {
                        $this->recursiveSetApiDataStructure($value, $included);
                        if (array_key_exists('type', $value)  && !empty($value['type']) && !empty($value['id'])) {
                            $included[] = $value;
                            unset($value);
                        }
                    } else {
                        $attributes[$key] = $value;
                    }
                }
            }
            $array = $this->buildApiDataStructureArray($type, $id, $attributes, $relationships, $links, $meta);
        }
    }

    /**
     * @param string $titleString
     * @param array $array
     * @return mixed
     */
    private function setUrlTitle($titleString, array &$array)
    {
        foreach($array as $key => $value) {
            $titleString = str_replace('{'.$key.'}', $value, $titleString);
        }
        return $titleString;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function getUrlReplacementKeys(array $array)
    {
        $keys = [];
        foreach($this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getIdProperties() as $k) {
            $keys[] = sprintf('{%s}', $k);
        }
        return $keys;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function getUrlReplacementValues(array $array)
    {
        $data = [];
        foreach($array as $k => &$v) {
            if(in_array($k, $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getIdProperties())) {
                if(is_array($v)) {
                    unset($v[Serializer::CLASS_IDENTIFIER_KEY]);
                    $data = array_merge($data, $v);
                } else {
                    $data[] = $v;
                }
            }
        }

        return $data;
    }

    /**
     * @param array $array
     * @param $key
     * @return bool
     */
    private function isIdentifierKey(array &$array, $key)
    {
        return array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $array)
        && null !== $array[Serializer::CLASS_IDENTIFIER_KEY]
        && true === in_array(
            $key,
            $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getIdProperties()
        );
    }

    /**
     * @param $value
     * @param $id
     * @return string
     */
    private function setIdentifierKey($value, $id)
    {
        if (is_array($value) && array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $value)) {
            unset($value[Serializer::CLASS_IDENTIFIER_KEY]);
            return implode('.', $value);
        }

        return $id;

    }

    /**
     * @param string $type
     * @param int|string $id
     * @param array $attributes
     * @param array $relationships
     * @param array $meta
     *
     * @return array
     */
    private function buildApiDataStructureArray($type, $id, array $attributes, array $relationships, array $links, array $meta)
    {

        $newData = ['type' => $type, 'id' => $id];

        if (!empty($attributes)) {
            $newData['attributes'] = $attributes;
        }

        if (!empty($relationships)) {
            $newData['relationships'] = $relationships;
        }

        if (!empty($links)) {
            $newData['links'] = $links;
        }

        if (!empty($meta)) {
            $newData['meta'] = $meta;
        }

        return $newData;
    }

    /**
     * @param array $array
     * @param array $included
     * @return array
     */
    private function buildResponse(array &$array, array &$included)
    {
        $response = [];

        if (!empty($this->apiVersion)) {
            $response['jsonapi']['version'] = $this->apiVersion;
        }

        if (!empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        if (!empty($array)) {
            $response['data'] = $array;
            if (!empty($this->included)) {
                $response['included'] = $this->included;
            }
        }

        if (!empty($included)) {
            $response['included'] = $included;
        }

        if (!empty($this->selfUrl)
            || !empty($this->firstUrl)
            || !empty($this->lastUrl)
            || !empty($this->prevUrl)
            || !empty($this->nextUrl)
            || !empty($this->relatedUrl)
        ) {
            $response['links'] = [
                'self' => $this->selfUrl,
                'first' => $this->firstUrl,
                'last' => $this->lastUrl,
                'prev' => $this->prevUrl,
                'next' => $this->nextUrl,
                'related' => $this->relatedUrl,
            ];
            $response['links'] = array_filter($response['links']);
        }

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return $response;
    }

    /**
     * @param string $key
     * @param array|string $value
     */
    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }
}
