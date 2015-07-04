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
                    'self' => '',
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
                            'href' => '',
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

        if (1 === count(array_unique($keys))) {
            $keyName = reset($keys);
            $array = [$this->namespaceAsArrayKey($keyName) => $array];
            return;
        } else {
            $array = $data;
        }
    }
}


$array = [];
for($i=1; $i<=5; $i++) {
    $array[] = new DateTime("now +$i days");
}

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
echo (new Serializer(new JsonApiTransformer()))->serialize($array);
echo PHP_EOL;
echo PHP_EOL;
echo '-------------------------------------------------------------';
echo 'JSON+HAL API Format';
echo '-------------------------------------------------------------';
echo PHP_EOL;
echo PHP_EOL;
echo (new Serializer(new JsonHalTransformer()))->serialize($array);