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
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $included = [];
        $this->recursiveSetValues($value);
        $data = $value;
        $this->recursiveData($data);
        $this->removeTopLevelDataFields($value, $data);
        $this->recursiveBuildIncluded($value, $included);

        return json_encode(
            [
                'data' => $data,
                'included' => $included
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        /*
         * luego el data, luego los relationship,y los included,
         * juntarlo todo en el mensaje final
         *
         *
         * esto en 4 funciones recursivas secuenciales.
         */

        die();
        $this->recursiveSetApiDataStructure($value, $included, $value);

        //@todo: Implmenent methods
        foreach ($this->mappings as $mapping) {
            //$this->recursiveUnsetClassKey($value, $mapping->getHiddenProperties(), $mapping->getClassName());
            //$this->recursiveRenameKeys($value, $mapping->getAliasedProperties(), $mapping->getClassName());
        }

        $this->recursiveUnset($value, [Serializer::CLASS_IDENTIFIER_KEY]);

        return json_encode(
            $this->buildResponse($value, $included),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
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

                       $this->recursiveData($value);


                        $relationships[] = [
                            $key => [
                                'data' => [
                                    'type' => '',
                                    'id' => '',
                                ],
                                'links' => [
                                    'self' => '',
                                ]
                            ]
                        ];

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

        //generico
        foreach($array as $include)
        {
            //mirar si cada propiedad es un array y si es asi, llamada recursiva a este metodo.

        }

        print_r($array); die();
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
     * @param $id
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
    private function setResponseVersion(&$response)
    {
        if (!empty($this->apiVersion)) {
            $response[self::JSONAPI_KEY][self::VERSION_KEY] = $this->apiVersion;
        }
    }

    /**
     * @param $response
     */
    private function setResponseMeta(&$response)
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
