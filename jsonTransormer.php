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
        $this->recursiveSetValue($value);

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
     * @param array $array
     */
    private function recursiveSetValue(array &$array)
    {
        if (array_key_exists('@value', $array)) {
            $array = $array['@value'];
        }

        if (is_array($array)) {
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $this->recursiveSetValue($value);
                }
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

header('Content-Type: application/json');
echo $serializer->serialize($array);