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
    const SELF_LINK         = 'self';
    const TITLE             = 'title';
    const RELATIONSHIPS_KEY = 'relationships';
    const LINKS_KEY         = 'links';
    const TYPE_KEY          = 'type';
    const DATA_KEY          = 'data';
    const JSONAPI_KEY       = 'jsonapi';
    const META_KEY          = 'meta';
    const INCLUDED_KEY      = 'included';
    const VERSION_KEY       = 'version';
    const ATTRIBUTES_KEY    = 'attributes';
    const ID_KEY            = 'id';

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
     * @param array $value
     * @param array $data
     */
    private function setResponseDataTypeAndId(array $value, array &$data)
    {
        $type = $value[Serializer::CLASS_IDENTIFIER_KEY];
        $data[self::TYPE_KEY] = $this->namespaceAsArrayKey($type);

        $idProperties = $this->mappings[$type]->getIdProperties();
        $ids = [];
        foreach(array_keys($value) as $propertyName) {
            if (in_array($propertyName, $idProperties, true)) {
                $ids[] = $value[$propertyName];
            }
        }

        $data[self::ID_KEY] = $ids;
    }

    /**
     * @param array $array
     * @param array $data
     */
    private function setResponseDataAttributes(array $array, array &$data)
    {
        $attributes = [];
        foreach($array as $propertyName => $value) {

            if (is_array($value)
                && array_key_exists(Serializer::SCALAR_TYPE, $value)
                && array_key_exists(Serializer::SCALAR_VALUE, $value)
            ) {
                $attributes[$propertyName] = $value;
            }
        }

        $data[self::ATTRIBUTES_KEY] = $attributes;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $data = [];
        $this->setResponseVersion($data);
        $this->setResponseMeta($data);
        $this->setResponseDataTypeAndId($value, $data);
        $this->setResponseDataAttributes($value, $data);






        print_r($data);
        die();

        $originalValue = $value;
        $this->recursiveSetValues($value);

        $data = [];


        //Basic structure without attributes
        $data = [self::ID_KEY => $this->getId($value),  self::TYPE_KEY => $this->getType($value)];
        $type = $value[Serializer::CLASS_IDENTIFIER_KEY];
        unset($value[Serializer::CLASS_IDENTIFIER_KEY]);

        //
        $attributes = [];
        $included = [];
        $relationships = [];
        $links = [];

        foreach($value as $key => $attribute) {
            if(!is_array($attribute)) {
                $attributes[$key] = $attribute;
                unset($value[$key]);
                continue;
            }

            //nested attributes. Solving this is key.
            if(is_array($attribute) && array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $attribute)) {
                break;
                $this->recursiveSetValues($attribute);
                print_r($attribute);
                die();
                $included[] = $attribute;
                $relationships[] = [
                    $key => [
                        'data' => [
                            'type' => $attribute[self::TYPE_KEY],
                            'id' => $attribute[self::ID_KEY],
                        ],
                      //  'links' => $attribute[self::LINKS_KEY]
                    ]
                ];
            }
        }


        $idAttributes = [];
        $replacements = [];
        foreach($this->mappings[$type]->getIdProperties() as $attribute) {
            $attributeUnderscore = $this->camelCaseToUnderscore($attribute);
            $idAttributes[] = $this->getIdValues($value[$attributeUnderscore]);
            $replacements[] = '{'.$attribute.'}';
        }

        print_r($idAttributes);
        print_r($replacements);
        die();
        $links[self::SELF_LINK] = str_replace(
            $replacements,
            $idAttributes,
            $this->mappings[$type]->getResourceUrl()
        );

        print_r($links);

        $data[self::ATTRIBUTES_KEY] = $attributes;
        $data[self::LINKS_KEY] = $links;
        $data[self::RELATIONSHIPS_KEY] = $relationships;
        $data[self::INCLUDED_KEY] = $included;

        $this->setResponseLinks($data);

        print_r($value);

        //$this->removeTopLevelDataFields($value, $data);
        //$data = $value;
        //$this->recursiveData($data);

        //$this->recursiveBuildIncluded($value, $included);

        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    private function getType(array &$array)
    {

        return $this->namespaceAsArrayKey($array[Serializer::CLASS_IDENTIFIER_KEY]);


        return $type;
    }

    private function getId(array &$array) {
        $type = $array[Serializer::CLASS_IDENTIFIER_KEY];
        $keys = $this->mappings[$type]->getIdProperties();
        $id = [];
        foreach($array as $key => $value) {
            if(in_array($key, $keys, true)) {
                if(is_array($value)) {
                    $value = $this->getId($value);
                }
                $id[] = $value;
                unset($array[$key]);
            }
        }
        return implode('.', $id);
    }

    private function getIdValues(array &$array) {
        $type = $array[Serializer::CLASS_IDENTIFIER_KEY];
        $keys = $this->mappings[$type]->getIdProperties();
        $id = [];
        foreach($array as $key => $value) {
            if(in_array($key, $keys, true)) {
                if(is_array($value)) {
                    $value = $this->getId($value);
                }
                $id[] = $value;
                unset($array[$key]);
            }
        }
        return $id;
    }






    private function recursiveData(array &$array)
    {
        if (is_array($array)) {

            if(array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $array)) {
                $id            = [];
                $type          = null;
                $attributes    = [];
                $meta          = [];
                $relationships = [];
                $links         = [];

                foreach ($array as $key => &$value) {
                    if ($key === Serializer::CLASS_IDENTIFIER_KEY) {
                        $type = $this->namespaceAsArrayKey($value);
                        continue;
                    }

                    if ($this->isIdentifierKey($array, $key)) {
                        $id          = $this->setIdentifierKey($value, $id);
                        $meta        = $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getMetaData();
                        $resourceUrl = $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getResourceUrl();

                        $this->setSelfFromRecursion($array, $resourceUrl, $links);
                        $this->setLinkTitleFromRecursion($array, $links);

                        continue;
                    }

                    if (is_array($value)) {
                        if (false === array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $value)) {
                            $this->recursiveData($value);
                            if(!is_array($value)) {
                                $attributes = array_merge($attributes, $value);
                            } else {
                                $attributes = array_merge($attributes, [$key => $value]);
                            }

                        } else {
                            $currentId = $value;
                            $this->recursiveData($currentId);

                            $relationships[] = [
                                $key => [
                                    'data' => [
                                        'type' => $currentId[self::TYPE_KEY],
                                        'id' => $currentId[self::ID_KEY],
                                    ],
                                    'links' => $currentId[self::LINKS_KEY]
                                ]
                            ];
                        }
                    } else {
                        $attributes[$key] = $value;
                    }
                }
                $array = $this->buildApiDataStructureArray($type, $id, $attributes, $relationships, $links, $meta);
            }
        }
    }

    /**
     * @param array $array
     * @param array $data
     */
    private function removeTopLevelDataFields(array &$array, array $data)
    {
        if(array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $array)) {
            $type = $array[Serializer::CLASS_IDENTIFIER_KEY];

            foreach($this->mappings[$type]->getIdProperties() as $property) {
                unset($array[$property]);
            }

            foreach(array_keys($data[self::ATTRIBUTES_KEY]) as $key) {
                unset($array[$key]);
            }

            unset($array[Serializer::CLASS_IDENTIFIER_KEY]);
        }
    }


    /**
     * @param array $array
     * @param array $included
     */
    private function recursiveBuildIncluded(array &$array, array &$included)
    {
        //caso particular: si tiene key @type, añadirlo a include.
        if (array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $array)) {
            $included[] = $array;
            return;
        }

        //generico
        foreach($array as &$include) {
            if (is_array($include)) {
                if (array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $include)) {
                    $this->recursiveData($include);
                    $included[] = $include;
                } else {
                  $this->recursiveBuildIncluded($include, $included);
                }
            }
        }

    }


    /**
     * @param array $array
     * @param array $included
     */
    private function recursiveSetApiDataStructure(array &$array, array &$included)
    {
        if (is_array($array)) {
            $id            = [];
            $type          = null;
            $attributes    = [];
            $meta          = [];
            $relationships = [];
            $links         = [];

            foreach ($array as $key => &$value) {
                if ($key === Serializer::CLASS_IDENTIFIER_KEY) {
                    $type = $this->namespaceAsArrayKey($value);
                    continue;
                }

                if ($this->isIdentifierKey($array, $key)) {
                    $id          = $this->setIdentifierKey($value);
                    $meta        = $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getMetaData();
                    $resourceUrl = $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getResourceUrl();

                    $this->setSelfFromRecursion($array, $resourceUrl, $links);
                    $this->setLinkTitleFromRecursion($array, $links);

                    continue;
                }

                if (is_array($value)) {

                    $this->recursiveSetApiDataStructure($value, $included);


                    if ($this->isIncludeable($value)) {


                        if(array_key_exists($value['type'].'.'.$value['id'], $included)) {
                            $included[$value['type'].'.'.$value['id']] = array_merge_recursive(
                                $included[$value['type'].'.'.$value['id']],
                                $value
                            );
                        } else {
                            $included[$value['type'].'.'.$value['id']] = $value;
                        }


                        $this->setRelationshipFromRecursion($key, $value, $relationships);
                    }
                    elseif (isset($value[self::RELATIONSHIPS_KEY])) {
                        foreach ($value[self::RELATIONSHIPS_KEY] as $relation) {
                            $this->setRelationshipFromRecursion(
                                $relation[self::DATA_KEY][self::TYPE_KEY],
                                $relation[self::DATA_KEY],
                                $relationships
                            );
                        }
                    } else {

                        if(!empty($parentType) && !empty($parentId)) {
                            $this->recursiveSetValues($parentArray);
                            $this->recursiveUnset($parentArray, [Serializer::CLASS_IDENTIFIER_KEY]);

                            $included[$parentType.'.'.$parentId][self::ATTRIBUTES_KEY][$key] = $value[self::ATTRIBUTES_KEY];
                        }
                    }

                } else {
                    $attributes[$key] = $value;
                }
            }

            print_r($attributes);

            $array = $this->buildApiDataStructureArray($type, $id, $attributes, $relationships, $links, $meta);
        }
    }

    /**
     * @param array $array
     * @param       $key
     *
     * @return bool
     */
    private function isIdentifierKey(array &$array, $key)
    {
        return array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $array)
        && null !== $array[Serializer::CLASS_IDENTIFIER_KEY]
        && array_key_exists($array[Serializer::CLASS_IDENTIFIER_KEY], $this->mappings)
        && in_array($key, $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getIdProperties());
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function setIdentifierKey($value)
    {
        if (is_array($value) && array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $value)) {
            unset($value[Serializer::CLASS_IDENTIFIER_KEY]);

            return implode('.', $value);
        }
    }

    /**
     * @param array $array
     * @param       $resourceUrl
     * @param array $links
     */
    private function setSelfFromRecursion(array &$array, $resourceUrl, array &$links)
    {
        if (!empty($resourceUrl)) {
            $replacementKeys        = $this->getUrlReplacementKeys($array);
            $replacementValues      = $this->getUrlReplacementValues($array);
            $links[self::SELF_LINK] = str_replace($replacementKeys, $replacementValues, $resourceUrl);
        }
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function getUrlReplacementKeys(array &$array)
    {
        $keys = [];
        foreach ($this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getIdProperties() as $k) {
            $keys[] = sprintf('{%s}', $k);
        }

        return $keys;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function getUrlReplacementValues(array &$array)
    {
        $data = [];
        foreach ($array as $k => &$v) {
            if (in_array($k, $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getIdProperties())) {
                if (is_array($v)) {
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
     * @param array $links
     */
    private function setLinkTitleFromRecursion(array &$array, array &$links)
    {
        $title = $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getResourceUrlTitlePattern();
        if (!empty($title)) {
            $links[self::TITLE] = $this->setUrlTitle($title, $array);
        }
    }

    /**
     * @param string $titleString
     * @param array  $array
     *
     * @return mixed
     */
    private function setUrlTitle($titleString, array &$array)
    {
        foreach ($array as $key => $value) {
            $titleString = str_replace('{' . $key . '}', $value, $titleString);
        }

        return $titleString;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isIncludeable($value)
    {
        return array_key_exists(self::TYPE_KEY, $value)
        && !empty($value[self::TYPE_KEY]) && !empty($value[self::ID_KEY]);
    }

    /**
     * @param       $key
     * @param       $value
     * @param array $relationships
     *
     * @return array
     */
    private function setRelationshipFromRecursion($key, $value, array &$relationships)
    {
        $arrayKey                                  = $this->camelCaseToUnderscore($key);
        $relationships[$arrayKey][self::LINKS_KEY] = [
            self::SELF_LINK => 'aa',
            'related'       => 'aa',
        ];

        if (array_key_exists($value[self::TYPE_KEY], $this->mappings)) {
            $relationships[$arrayKey][self::LINKS_KEY] = $this->mappings[$value[self::TYPE_KEY]]->getRelationships();
        }

        $relationships[$arrayKey][self::DATA_KEY] = $this->buildApiDataStructureArray(
            $value[self::TYPE_KEY],
            $value[self::ID_KEY],
            [],
            [],
            [],
            []
        );

        return $relationships;
    }

    /**
     * @param       $type
     * @param       $id
     * @param array $attributes
     * @param array $relationships
     * @param array $links
     * @param array $meta
     *
     * @return array
     */
    private function buildApiDataStructureArray(
        $type,
        $id,
        array $attributes,
        array $relationships,
        array $links,
        array $meta
    ) {
        $newData = [self::TYPE_KEY => $type, self::ID_KEY => $id];

        if (!empty($attributes)) {
            $newData[self::ATTRIBUTES_KEY] = $attributes;
        }

        if (!empty($links)) {
            $newData[self::LINKS_KEY] = $links;
        }

        if (!empty($relationships)) {
            $newData[self::RELATIONSHIPS_KEY] = $relationships;
        }

        if (!empty($meta)) {
            $newData[self::META_KEY] = $meta;
        }

        return $newData;
    }

    /**
     * @param array $array
     * @param array $included
     *
     * @return array
     */
    private function buildResponse(array &$array, array &$included)
    {
        $response = [];

        $this->setResponseVersion($response);
        $this->setResponseMeta($response);
        $this->setResponseData($array, $included, $response);
        $this->setResponseLinks($response);

        return $response;
    }

    /**
     * @param $response
     */
    private function setResponseVersion(array &$response)
    {
        if (!empty($this->apiVersion)) {
            $response[self::JSONAPI_KEY][self::VERSION_KEY] = $this->apiVersion;
        }
    }

    /**
     * @param $response
     */
    private function setResponseMeta(array &$response)
    {
        if (!empty($this->meta)) {
            $response[self::META_KEY] = $this->meta;
        }
    }

    /**
     * @param array $array
     * @param array $included
     * @param       $response
     */
    private function setResponseData(array &$array, array &$included, &$response)
    {
        if (!empty($array)) {
            $response[self::DATA_KEY] = $array;
            if (!empty($included)) {
                $response[self::INCLUDED_KEY] = array_values($included);
            }
        }
    }

    /**
     * @param $response
     */
    private function setResponseLinks(&$response)
    {
        if (!empty($this->selfUrl)
            || !empty($this->firstUrl)
            || !empty($this->lastUrl)
            || !empty($this->prevUrl)
            || !empty($this->nextUrl)
            || !empty($this->relatedUrl)
        ) {
            $response[self::LINKS_KEY] = [
                self::SELF_LINK => $this->selfUrl,
                'first'         => $this->firstUrl,
                'last'          => $this->lastUrl,
                'prev'          => $this->prevUrl,
                'next'          => $this->nextUrl,
                'related'       => $this->relatedUrl,
            ];
            $response[self::LINKS_KEY] = array_filter($response[self::LINKS_KEY]);
        }
    }

    /**
     * @param string       $key
     * @param array|string $value
     */
    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }

}
