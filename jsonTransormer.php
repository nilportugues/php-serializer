<?php
use NilPortugues\Serializer\Serializer;
use NilPortugues\Serializer\Strategy\StrategyInterface;

include 'vendor/autoload.php';

/**
 * Class AbstractTransformer
 */
abstract class AbstractTransformer implements StrategyInterface
{
    protected $mappings = [];
    /**
     * @param array $apiMappings
     */
    public function __construct(array $apiMappings)
    {
        $this->mappings = $apiMappings;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    abstract public function serialize($value);

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

    /**
     * @param array $array
     * @param array $unwantedKey
     */
    protected function recursiveUnset(array &$array, array $unwantedKey) {

        foreach ($unwantedKey as $key) {
            if(array_key_exists($key, $array)) {
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
     * @param array    $array       Array with data
     * @param string   $typeKey     Scope to do the replacement.
     * @param string   $key         Name of the key holding the value to replace
     * @param \Closure $callable    Callable with replacement logic
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
     * @param $value
     *
     * @throws InvalidArgumentException
     * @return array
     */
    public function unserialize($value)
    {
        throw new \InvalidArgumentException('JsonTransformer does not perform unserializations.');
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
     * @var string
     */
    private $selfUrl;

    /**
     * @param string $self
     * @throws \InvalidArgumentException
     */
    public function setSelfUrl($self)
    {
        if (false === filter_var($self, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Provided value is not a valid URL');
        }

        $this->selfUrl = (string) $self;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $this->recursiveSetValues($value);
        $this->recursiveSetTypeAsKey($value);

        return json_encode(
            [
                'data' => $value,
                'links' => [
                    'self' => $this->selfUrl,
                ],
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
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
     * @var string
     */
    private $selfUrl;

    /**
     * @var string
     */
    private $nextUrl;

    /**
     * @param string $self
     * @throws \InvalidArgumentException
     */
    public function setSelfUrl($self)
    {
        if (false === filter_var($self, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Provided value is not a valid URL');
        }

        $this->selfUrl = (string) $self;
    }

    public function setCuries(array $curies)
    {

    }

    public function addCury($name, array $curi)
    {

    }

    /**
     * @param $nextUrl
     * @throws \InvalidArgumentException
     */
    public function setNextUrl($nextUrl)
    {
        if (false === filter_var($nextUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Provided value is not a valid URL');
        }
        $this->nextUrl = (string) $nextUrl;
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
                        'curies' => [],
                        'next' => [
                            'href' => '',
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
        foreach($array as $value) {
            if(is_array($value) && array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $value)) {
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
     * @param $className
     * @param null $resourceUrlPattern
     * @param array $idProperties
     */
    public function __construct($className, $resourceUrlPattern = null, array $idProperties = [])
    {
        $this->className = (string) $className;
        $this->resourceUrlPattern = (string) $resourceUrlPattern;
        $this->idProperties = $idProperties;
    }

    /**
     * @param $idProperty
     */
    public function addIdProperty($idProperty)
    {
        $this->idProperties[] = (string) $idProperty;
    }

    /**
     * @param array $idProperties
     */
    public function setIdProperties(array $idProperties)
    {
        $this->idProperties = array_merge($this->idProperties, $idProperties);
    }

    /**
     * @param string $resourceUrlPattern
     */
    public function setResourceUrlPattern($resourceUrlPattern)
    {
        $this->resourceUrlPattern = (string) $resourceUrlPattern;
    }

    /**
     * @param array $hidden
     */
    public function setHiddenProperties(array $hidden)
    {
        $this->hiddenProperties = array_merge($this->hiddenProperties, array_values($hidden));
    }

    /**
     * @param string $propertyName
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
}

$array = [];
for($i=1; $i<=5; $i++) {
    $array[] = new DateTime("now +$i days");
}


$dateTimeMapping = new ApiMapping('DateTime', 'http://example.com/date-time/$s', ['timezone_type']);
$dateTimeMapping->setHiddenProperties(['timezone_type']);
$dateTimeMapping->setPropertyNameAliases(['date' => 'fecha']);

$apiMappingCollection = [
    $dateTimeMapping
];

header('Content-Type: application/json');

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
$serializer->setSelfUrl('http://example.com/date_time/');

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

echo (new Serializer($serializer))->serialize($array);