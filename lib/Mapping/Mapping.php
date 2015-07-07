<?php

namespace NilPortugues\Api\Mapping;

class Mapping
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
     * @param null  $resourceUrlPattern
     * @param array $idProperties
     */
    public function __construct($className, $resourceUrlPattern = null, array $idProperties = [])
    {
        $this->className = (string) $className;
        $this->resourceUrlPattern = (string) $resourceUrlPattern;
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
        $this->idProperties[] = (string) $idProperty;
    }

    /**
     * @param string $resourceUrlPattern
     */
    public function setResourceUrlPattern($resourceUrlPattern)
    {
        $this->resourceUrlPattern = (string) $resourceUrlPattern;
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
