<?php
use NilPortugues\Serializer\Serializer;
use NilPortugues\Serializer\Strategy\StrategyInterface;

include 'vendor/autoload.php';

class JsonTransformer implements StrategyInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $this->recursiveUnset($value, ['@type', '@scalar']);

        return json_encode($value, JSON_PRETTY_PRINT);
    }

    /**
     * @param array $array
     * @param array $unwantedKey
     */
    private function recursiveUnset(array &$array, array $unwantedKey) {

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


$array = [];
for($i=1; $i<=5; $i++) {
    $array[] = new DateTime("now +$i days");
}

$serializer = new Serializer(new JsonTransformer());

print_r($serializer->serialize($array));