<?php

namespace NilPortugues\Serializer;

use Closure;
use NilPortugues\Serializer\Serializer\InternalClasses\SplFixedArraySerializer;
use NilPortugues\Serializer\Strategy\StrategyInterface;
use ReflectionClass;
use ReflectionException;
use SplObjectStorage;

class Serializer
{
    const CLASS_IDENTIFIER_KEY = '@type';
    const CLASS_PARENT_KEY = '@parent';
    const SCALAR_TYPE = '@scalar';
    const SCALAR_VALUE = '@value';
    const NULL_VAR = null;
    const MAP_TYPE = '@map';

    /**
     * Storage for object.
     *
     * Used for recursion
     *
     * @var SplObjectStorage
     */
    protected static $objectStorage;

    /**
     * Object mapping for recursion.
     *
     * @var array
     */
    protected static $objectMapping = [];

    /**
     * Object mapping index.
     *
     * @var int
     */
    protected static $objectMappingIndex = 0;

    /**
     * @var \NilPortugues\Serializer\Strategy\StrategyInterface|\NilPortugues\Serializer\Strategy\JsonStrategy
     */
    protected $serializationStrategy;

    /**
     * @var array
     */
    private $dateTimeClassType = ['DateTime', 'DateTimeImmutable', 'DateTimeZone', 'DateInterval', 'DatePeriod'];

    /**
     * @var array
     */
    protected $serializationMap = [
        'array' => 'serializeArray',
        'integer' => 'serializeScalar',
        'double' => 'serializeScalar',
        'boolean' => 'serializeScalar',
        'string' => 'serializeScalar',
    ];

    /**
     * Hack specific serialization classes.
     *
     * @var array
     */
    protected $unserializationMapHHVM = [];

    /**
     * @param StrategyInterface $strategy
     */
    public function __construct(StrategyInterface $strategy)
    {

        $this->serializationStrategy = $strategy;
    }

    /**
     * This is handly specially in order to add additional data before the
     * serialization process takes place using the transformer public methods, if any.
     *
     * @return StrategyInterface
     */
    public function getTransformer()
    {
        return $this->serializationStrategy;
    }

    /**
     * Serialize the value in JSON.
     *
     * @param mixed $value
     *
     * @return string JSON encoded
     *
     * @throws SerializerException
     */
    public function serialize($value)
    {
        $this->reset();

        return $this->serializationStrategy->serialize($this->serializeData($value));
    }

    /**
     * Reset variables.
     */
    protected function reset()
    {
        self::$objectStorage = new SplObjectStorage();
        self::$objectMapping = [];
        self::$objectMappingIndex = 0;
    }

    /**
     * Parse the data to be json encoded.
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws SerializerException
     */
    protected function serializeData($value)
    {
        $this->guardForUnsupportedValues($value);

        if ($this->isInstanceOf($value, 'SplFixedArray')) {
            return SplFixedArraySerializer::serialize($this, $value);
        }

        if (\is_object($value)) {
            return $this->serializeObject($value);
        }

        $type = (\gettype($value) && $value !== null) ? \gettype($value) : 'string';
        $func = $this->serializationMap[$type];

        return $this->$func($value);
    }

    /**
     * Check if a class is instance or extends from the expected instance.
     *
     * @param mixed  $value
     * @param string $classFQN
     *
     * @return bool
     */
    private function isInstanceOf($value, $classFQN)
    {
        return is_object($value)
        && (strtolower(get_class($value)) === strtolower($classFQN) || \is_subclass_of($value, $classFQN, true));
    }

    /**
     * @param mixed $value
     *
     * @throws SerializerException
     */
    protected function guardForUnsupportedValues($value)
    {
        if ($value instanceof Closure) {
            throw new SerializerException('Closures are not supported in Serializer');
        }

        if ($value instanceof \DatePeriod) {
            throw new SerializerException(
                'DatePeriod is not supported in Serializer. Loop through it and serialize the output.'
            );
        }

        if (\is_resource($value)) {
            throw new SerializerException('Resource is not supported in Serializer');
        }
    }

    /**
     * Unserialize the value from string.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function unserialize($value)
    {
        if (\is_array($value) && isset($value[self::SCALAR_TYPE])) {
            return $this->unserializeData($value);
        }

        $this->reset();

        return $this->unserializeData($this->serializationStrategy->unserialize($value));
    }

    /**
     * Parse the json decode to convert to objects again.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function unserializeData($value)
    {
        if ($value === null || !is_array($value)) {
            return $value;
        }

        if (isset($value[self::MAP_TYPE]) && !isset($value[self::CLASS_IDENTIFIER_KEY])) {
            $value = $value[self::SCALAR_VALUE];

            return $this->unserializeData($value);
        }

        if (isset($value[self::SCALAR_TYPE])) {
            return $this->getScalarValue($value);
        }

        if (isset($value[self::CLASS_PARENT_KEY]) && 0 === strcmp($value[self::CLASS_PARENT_KEY], 'SplFixedArray')) {
            return SplFixedArraySerializer::unserialize($this, $value[self::CLASS_IDENTIFIER_KEY], $value);
        }

        if (isset($value[self::CLASS_IDENTIFIER_KEY])) {
            return $this->unserializeObject($value);
        }

        return \array_map([$this, __FUNCTION__], $value);
    }

    /**
     * @param $value
     *
     * @return float|int|null|bool
     */
    protected function getScalarValue($value)
    {
        switch ($value[self::SCALAR_TYPE]) {
            case 'integer':
                return \intval($value[self::SCALAR_VALUE]);
            case 'float':
                return \floatval($value[self::SCALAR_VALUE]);
            case 'boolean':
                return $value[self::SCALAR_VALUE];
            case 'NULL':
                return self::NULL_VAR;
        }

        return $value[self::SCALAR_VALUE];
    }

    /**
     * Convert the serialized array into an object.
     *
     * @param array $value
     *
     * @return object
     *
     * @throws SerializerException
     */
    protected function unserializeObject(array $value)
    {
        $className = $value[self::CLASS_IDENTIFIER_KEY];
        unset($value[self::CLASS_IDENTIFIER_KEY]);

        if (isset($value[self::MAP_TYPE])) {
            unset($value[self::MAP_TYPE]);
            unset($value[self::SCALAR_VALUE]);
        }

        if ($className[0] === '@') {
            return self::$objectMapping[substr($className, 1)];
        }

        if (!class_exists($className)) {
            throw new SerializerException('Unable to find class '.$className);
        }

        return (null === ($obj = $this->unserializeDateTimeFamilyObject($value, $className)))
            ? $this->unserializeUserDefinedObject($value, $className) : $obj;
    }

    /**
     * @param array  $value
     * @param string $className
     *
     * @return mixed
     */
    protected function unserializeDateTimeFamilyObject(array $value, $className)
    {
        $obj = null;

        if ($this->isDateTimeFamilyObject($className)) {
            $obj = $this->restoreUsingUnserialize($className, $value);
            self::$objectMapping[self::$objectMappingIndex++] = $obj;
        }

        return $obj;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    protected function isDateTimeFamilyObject($className)
    {
        $isDateTime = false;

        foreach ($this->dateTimeClassType as $class) {
            $isDateTime = $isDateTime || \is_subclass_of($className, $class, true) || $class === $className;
        }

        return $isDateTime;
    }

    /**
     * @param string $className
     * @param array  $attributes
     *
     * @return mixed
     */
    protected function restoreUsingUnserialize($className, array $attributes)
    {
        foreach ($attributes as &$attribute) {
            $attribute = $this->unserializeData($attribute);
        }

        $obj = (object) $attributes;
        $serialized = \preg_replace(
            '|^O:\d+:"\w+":|',
            'O:'.strlen($className).':"'.$className.'":',
            \serialize($obj)
        );

        return \unserialize($serialized);
    }

    /**
     * @param array  $value
     * @param string $className
     *
     * @return object
     */
    protected function unserializeUserDefinedObject(array $value, $className)
    {
        $ref = new ReflectionClass($className);
        $obj = $ref->newInstanceWithoutConstructor();

        self::$objectMapping[self::$objectMappingIndex++] = $obj;
        $this->setUnserializedObjectProperties($value, $ref, $obj);

        if (\method_exists($obj, '__wakeup')) {
            $obj->__wakeup();
        }

        return $obj;
    }

    /**
     * @param array           $value
     * @param ReflectionClass $ref
     * @param mixed           $obj
     *
     * @return mixed
     */
    protected function setUnserializedObjectProperties(array $value, ReflectionClass $ref, $obj)
    {
        foreach ($value as $property => $propertyValue) {
            try {
                $propRef = $ref->getProperty($property);
                $propRef->setAccessible(true);
                $propRef->setValue($obj, $this->unserializeData($propertyValue));
            } catch (ReflectionException $e) {
                $obj->$property = $this->unserializeData($propertyValue);
            }
        }

        return $obj;
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function serializeScalar($value)
    {
        $type = \gettype($value);
        if ($type === 'double') {
            $type = 'float';
        }

        return [
            self::SCALAR_TYPE => $type,
            self::SCALAR_VALUE => $value,
        ];
    }

    /**
     * @param array $value
     *
     * @return array
     */
    protected function serializeArray(array $value)
    {
        if (\array_key_exists(self::MAP_TYPE, $value)) {
            return $value;
        }

        $toArray = [self::MAP_TYPE => 'array', self::SCALAR_VALUE => []];
        foreach ($value as $key => $field) {
            $toArray[self::SCALAR_VALUE][$key] = $this->serializeData($field);
        }

        return $this->serializeData($toArray);
    }

    /**
     * Extract the data from an object.
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function serializeObject($value)
    {
        if (self::$objectStorage->contains($value)) {
            return [self::CLASS_IDENTIFIER_KEY => '@'.self::$objectStorage[$value]];
        }

        self::$objectStorage->attach($value, self::$objectMappingIndex++);

        $reflection = new ReflectionClass($value);
        $className = $reflection->getName();

        return $this->serializeInternalClass($value, $className, $reflection);
    }

    /**
     * @param mixed           $value
     * @param string          $className
     * @param ReflectionClass $ref
     *
     * @return array
     */
    protected function serializeInternalClass($value, $className, ReflectionClass $ref)
    {
        $paramsToSerialize = $this->getObjectProperties($ref, $value);
        $data = [self::CLASS_IDENTIFIER_KEY => $className];
        $data += \array_map([$this, 'serializeData'], $this->extractObjectData($value, $ref, $paramsToSerialize));

        return $data;
    }

    /**
     * Return the list of properties to be serialized.
     *
     * @param ReflectionClass $ref
     * @param $value
     *
     * @return array
     */
    protected function getObjectProperties(ReflectionClass $ref, $value)
    {
        $props = [];
        foreach ($ref->getProperties() as $prop) {
            $props[] = $prop->getName();
        }

        return \array_unique(\array_merge($props, \array_keys(\get_object_vars($value))));
    }

    /**
     * Extract the object data.
     *
     * @param mixed            $value
     * @param \ReflectionClass $rc
     * @param array            $properties
     *
     * @return array
     */
    protected function extractObjectData($value, ReflectionClass $rc, array $properties)
    {
        $data = [];

        $this->extractCurrentObjectProperties($value, $rc, $properties, $data);
        $this->extractAllInhertitedProperties($value, $rc, $data);

        return $data;
    }

    /**
     * @param mixed           $value
     * @param ReflectionClass $rc
     * @param array           $properties
     * @param array           $data
     */
    protected function extractCurrentObjectProperties($value, ReflectionClass $rc, array $properties, array &$data)
    {
        foreach ($properties as $propertyName) {
            try {
                $propRef = $rc->getProperty($propertyName);
                $propRef->setAccessible(true);
                $data[$propertyName] = $propRef->getValue($value);
            } catch (ReflectionException $e) {
                $data[$propertyName] = $value->$propertyName;
            }
        }
    }

    /**
     * @param mixed           $value
     * @param ReflectionClass $rc
     * @param array           $data
     */
    protected function extractAllInhertitedProperties($value, ReflectionClass $rc, array &$data)
    {
        do {
            $rp = array();
            /* @var $property \ReflectionProperty */
            foreach ($rc->getProperties() as $property) {
                $property->setAccessible(true);
                $rp[$property->getName()] = $property->getValue($value);
            }
            $data = \array_merge($rp, $data);
        } while ($rc = $rc->getParentClass());
    }
}
