<?php
use NilPortugues\Serializer\Serializer;
use NilPortugues\Serializer\Strategy\StrategyInterface;

include 'vendor/autoload.php';

/**
 * Class AbstractTransformer
 */
abstract class AbstractTransformer implements StrategyInterface
{
    /**
     * @var ApiMapping[]
     */
    protected $mappings = [];
    /**
     * @var string
     */
    protected $firstUrl = '';
    /**
     * @var string
     */
    protected $lastUrl = '';
    /**
     * @var string
     */
    protected $prevUrl = '';
    /**
     * @var string
     */
    protected $nextUrl = '';
    /**
     * @var string
     */
    protected $selfUrl = '';

    /**
     * @param array $apiMappings
     */
    public function __construct(array $apiMappings)
    {
        $this->mappings = $apiMappings;
    }

    /**
     * @param string $self
     *
     * @throws \InvalidArgumentException
     */
    public function setSelfUrl($self)
    {
        $this->validateUrl($self);
        $this->selfUrl = (string)$self;
    }

    /**
     * @param $url
     *
     * @throws InvalidArgumentException
     */
    protected function validateUrl($url)
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Provided value is not a valid URL');
        }
    }

    /**
     * @param string $firstUrl
     *
     * @throws \InvalidArgumentException
     */
    public function setFirstUrl($firstUrl)
    {
        $this->validateUrl($firstUrl);
        $this->firstUrl = (string)$firstUrl;
    }

    /**
     * @param string $lastUrl
     *
     * @throws \InvalidArgumentException
     */
    public function setLastUrl($lastUrl)
    {
        $this->validateUrl($lastUrl);
        $this->lastUrl = (string)$lastUrl;
    }

    /**
     * @param $nextUrl
     *
     * @throws \InvalidArgumentException
     */
    public function setNextUrl($nextUrl)
    {
        $this->validateUrl($nextUrl);
        $this->nextUrl = (string)$nextUrl;
    }

    /**
     * @param $prevUrl
     *
     * @throws \InvalidArgumentException
     */
    public function setPrevUrl($prevUrl)
    {
        $this->validateUrl($prevUrl);
        $this->prevUrl = (string)$prevUrl;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    abstract public function serialize($value);

    /**
     * @param $value
     *
     * @throws InvalidArgumentException
     * @return array
     */
    public function unserialize($value)
    {
        throw new \InvalidArgumentException('JsonTransformer does not perform unserializations.');
    }

    /**
     * Converts a underscore string to camelCase.
     *
     * @param string $string
     *
     * @return string
     */
    protected function underscoreToCamelCase($string)
    {
        return str_replace(" ", "", ucwords(strtolower(str_replace(["_", "-"], " ", $string))));
    }

    /**
     * @param array $array
     * @param array $unwantedKey
     */
    protected function recursiveUnset(array &$array, array $unwantedKey)
    {

        foreach ($unwantedKey as $key) {
            if (array_key_exists($key, $array)) {
                unset($array[$key]);
            }
        }

        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveUnset($value, $unwantedKey);
            }
        }
    }

    /**
     * @param array $array
     */
    protected function recursiveSetValues(array &$array)
    {
        if (array_key_exists('@value', $array)) {
            $array = $array['@value'];
        }

        if (is_array($array)) {
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $this->recursiveSetValues($value);
                }
            }
        }
    }

    /**
     * @param array $array
     * @param array $replaceMap
     */
    protected function recursiveChangeKeyNames(array &$array, array $replaceMap)
    {

    }

    /**
     * Renames a key in an array.
     *
     * @param array $array Array with data
     * @param string $typeKey Scope to do the replacement.
     * @param string $key Name of the key holding the value to replace
     * @param \Closure $callable Callable with replacement logic
     */
    protected function recursiveChangeKeyValue(array &$array, $typeKey, $key, \Closure $callable)
    {

    }

    /**
     * Adds a value to an existing identifiable entity containing @type.
     *
     * @param array $array
     * @param       $typeKey
     * @param array $value
     */
    protected function recursiveAddValue(array &$array, $typeKey, array $value)
    {

    }


    /**
     * Array's type value becomes the key of the provided array.
     *
     * @param array $array
     */
    protected function recursiveSetTypeAsKey(array &$array)
    {
        if (is_array($array)) {
            foreach ($array as &$value) {
                if (!empty($value[Serializer::CLASS_IDENTIFIER_KEY])) {
                    $key = $value[Serializer::CLASS_IDENTIFIER_KEY];
                    unset($value[Serializer::CLASS_IDENTIFIER_KEY]);
                    $value = [$this->namespaceAsArrayKey($key) => $value];

                    $this->recursiveSetTypeAsKey($value);
                }

            }
        }
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function namespaceAsArrayKey($key)
    {
        $keys = explode("\\", $key);
        $className = end($keys);

        return $this->camelCaseToUnderscore($className);
    }

    /**
     * @param        $camel
     * @param string $splitter
     *
     * @return string
     */
    protected function camelCaseToUnderscore($camel, $splitter = "_")
    {
        $camel = preg_replace(
            '/(?!^)[[:upper:]][[:lower:]]/',
            '$0',
            preg_replace('/(?!^)[[:upper:]]+/', $splitter . '$0', $camel)
        );

        return strtolower($camel);
    }
}

/**
 * Class JsonTransformer
 */
class JsonTransformer extends AbstractTransformer
{
    public function __construct()
    {
        //overwriting default constructor.
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $this->recursiveSetValues($value);

        $this->recursiveUnset($value, ['@type']);

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

}

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
        $this->recursiveSetValues($value);
        $this->recursiveSetApiDataStructure($value);
        $this->firstAttributeLevelKeyToDataKey($value);

        //@todo: Implmenent methods
        foreach ($this->mappings as $mapping) {
            //$this->recursiveUnsetClassKey($value, $mapping->getHiddenProperties(), $mapping->getClassName());
            //$this->recursiveRenameKeys($value, $mapping->getAliasedProperties(), $mapping->getClassName());
        }

        $this->recursiveUnset($value, ['@type']);

        return json_encode(
            $this->buildResponse($value),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param array $array
     */
    private function recursiveSetApiDataStructure(array &$array)
    {
        if (is_array($array)) {

            $id = [];
            $type = null;
            $attributes = [];

            foreach ($array as $key => $value) {

                if ($key === Serializer::CLASS_IDENTIFIER_KEY) {
                    $type = $this->namespaceAsArrayKey($value);
                } elseif (!empty($array[Serializer::CLASS_IDENTIFIER_KEY])
                    && true === in_array(
                        $key,
                        $this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getIdProperties()
                    )
                ) {

                    if (1 === count($this->mappings[$array[Serializer::CLASS_IDENTIFIER_KEY]]->getIdProperties())) {
                        $id = $value;
                    } else {
                        $id = array_merge($id, [$key => $value]);
                    }
                } else {
                    $attributes[$key] = $value;
                    unset($array[$key]);
                    if (is_array($value)) {
                        $this->recursiveSetApiDataStructure($attributes[$key]);
                    }
                }
            }

            $array = [
                'type' => $type,
                'id' => $id,
                'attributes' => $attributes,
                'relationships' => $this->relationships,
                'meta' => [
                    ''
                ],
            ];

        }
    }

    /**
     * @param array $array
     */
    private function firstAttributeLevelKeyToDataKey(array &$array)
    {
        if (false !== empty($array['data']['attributes'])) {
            $array = $array['attributes'];

        }
    }

    /**
     * @param array $array
     * @return array
     */
    private function buildResponse(array &$array)
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
     * @param        $value
     */
    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }
}


/**
 * This Transformer follows the JSON+HAL specification.
 *
 * @link http://stateless.co/hal_specification.html
 */
class JsonHalTransformer extends AbstractTransformer
{

    /**
     * @var
     */
    private $curies = [];

    /**
     * @param array $curies
     */
    public function setCuries(array $curies)
    {
        $this->curies = array_merge($this->curies, $curies);
    }

    /**
     * @param       $name
     * @param array $curie
     */
    public function addCurie($name, array $curie)
    {
        $this->curies[$name] = $curie;
    }


    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $this->recursiveSetValues($value);
        $this->groupValuesOrMoveOneLevelUp($value);


        $this->recursiveUnset($value, ['@type']);

        return json_encode(
            array_merge(
                $value,
                [
                    '_links' => [
                        'self' => [
                            'href' => $this->selfUrl,
                        ],
                        'curies' => $this->curies,
                        'first' => [
                            'href' => $this->firstUrl,
                        ],
                        'last' => [
                            'href' => $this->lastUrl,
                        ],
                        'next' => [
                            'href' => $this->nextUrl,
                        ],
                        'prev' => [
                            'href' => $this->prevUrl,
                        ],
                    ],
                    '_embedded' => [],
                ]
            ),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param array $array
     */
    private function groupValuesOrMoveOneLevelUp(array &$array)
    {
        $keys = [];
        $data = [];
        foreach ($array as $value) {
            if (is_array($value) && array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $value)) {
                $keys[] = $value[Serializer::CLASS_IDENTIFIER_KEY];
            } else {
                $data[$this->namespaceAsArrayKey($value[Serializer::CLASS_IDENTIFIER_KEY])] = $value;
                $keys[] = null;
            }
        }
        $keys = array_unique($keys);

        if (1 === count($keys)) {
            $keyName = reset($keys);
            $array = [$this->namespaceAsArrayKey($keyName) => $array];
        } else {
            $array = $data;
        }
    }
}


class ApiMapping
{
    /**
     * @var array
     */
    private $aliasedProperties = [];
    /**
     * @var array
     */
    private $hiddenProperties = [];

    /**
     * @var array
     */
    private $idProperties = [];

    /**
     * @param       $className
     * @param null $resourceUrlPattern
     * @param array $idProperties
     */
    public function __construct($className, $resourceUrlPattern = null, array $idProperties = [])
    {
        $this->className = (string)$className;
        $this->resourceUrlPattern = (string)$resourceUrlPattern;
        $this->idProperties = $idProperties;
    }

    /**
     * @return array
     */
    public function getIdProperties()
    {
        return $this->idProperties;
    }

    /**
     * @param array $idProperties
     */
    public function setIdProperties(array $idProperties)
    {
        $this->idProperties = array_merge($this->idProperties, $idProperties);
    }

    /**
     * @param $idProperty
     */
    public function addIdProperty($idProperty)
    {
        $this->idProperties[] = (string)$idProperty;
    }

    /**
     * @param string $resourceUrlPattern
     */
    public function setResourceUrlPattern($resourceUrlPattern)
    {
        $this->resourceUrlPattern = (string)$resourceUrlPattern;
    }

    /**
     * @param string $propertyName
     *
     * @throws InvalidArgumentException
     */
    public function hideProperty($propertyName)
    {
        if (false === in_array($propertyName, $this->hiddenProperties, true)) {
            throw new InvalidArgumentException(
                sprintf('Property %s already to be hidden'),
                $propertyName
            );
        }
        $this->hiddenProperties[] = $propertyName;
    }

    /**
     * @param $propertyName
     * @param $propertyAlias
     */
    public function addPropertyAlias($propertyName, $propertyAlias)
    {
        $this->aliasedProperties[$propertyName] = $propertyAlias;
    }

    /**
     * @param array $properties
     */
    public function setPropertyNameAliases(array $properties)
    {
        $this->aliasedProperties = array_merge($this->aliasedProperties, $properties);
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return null
     */
    public function getResourceUrl()
    {
        return $this->resourceUrlPattern;
    }

    /**
     * @return array
     */
    public function getAliasedProperties()
    {
        return $this->aliasedProperties;
    }

    /**
     * @return array
     */
    public function getHiddenProperties()
    {
        return $this->hiddenProperties;
    }

    /**
     * @param array $hidden
     */
    public function setHiddenProperties(array $hidden)
    {
        $this->hiddenProperties = array_merge($this->hiddenProperties, array_values($hidden));
    }
}

$array = [];
for ($i = 1; $i <= 5; $i++) {
    $array[] = new DateTime("now +$i days");
}


$dateTimeMapping = new ApiMapping('DateTime', 'http://example.com/date-time/%s', ['timezone_type']);
$dateTimeMapping->setHiddenProperties(['timezone_type']);
$dateTimeMapping->setPropertyNameAliases(['date' => 'fecha']);

$apiMappingCollection = [
    $dateTimeMapping->getClassName() => $dateTimeMapping
];


header('Content-Type: application/vnd.api+json');


print_r($apiMappingCollection);
echo PHP_EOL;
echo PHP_EOL;

echo '-------------------------------------------------------------';
echo 'JSON Format';
echo '-------------------------------------------------------------';
echo PHP_EOL;
echo PHP_EOL;
echo (new Serializer(new JsonTransformer()))->serialize($array);
echo PHP_EOL;
echo PHP_EOL;
echo '-------------------------------------------------------------';
echo 'JSON API Format';
echo '-------------------------------------------------------------';
echo PHP_EOL;
echo PHP_EOL;
$serializer = new JsonApiTransformer($apiMappingCollection);
$serializer->setApiVersion('1.0.1');
$serializer->setSelfUrl('http://example.com/date_time/');
$serializer->setNextUrl('http://example.com/date_time/?page=2&amount=20');
$serializer->addMeta(
    'author',
    [
        ['name' => 'Nil Portugués Calderó', 'email' => 'contact@nilportugues.com']
    ]
);

echo (new Serializer($serializer))->serialize($array);
echo PHP_EOL;
echo PHP_EOL;
echo '-------------------------------------------------------------';
echo 'JSON+HAL API Format';
echo '-------------------------------------------------------------';
echo PHP_EOL;
echo PHP_EOL;

$serializer = new JsonHalTransformer($apiMappingCollection);
$serializer->setSelfUrl('http://example.com/date_time/');
$serializer->setNextUrl('http://example.com/date_time/?page=2&amount=20');

echo (new Serializer($serializer))->serialize($array);