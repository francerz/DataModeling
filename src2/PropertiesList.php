<?php

namespace Francerz\PhpModel;

class PropertiesList
{
    private $reportedIndexes = array();
    private $properties;

    /**
     * Instantiates this class
     */
    public function __construct()
    {
        $this->properties = array();
    }

    /**
     * Checks whether a property is on the list.
     *
     * @param string $property
     * @return bool Returns TRUE if property exists, FALSE otherwise.
     */
    public function exists($property)
    {
        return array_key_exists($property, $this->properties);
    }

    /**
     * Adds a new property to the list
     * 
     * If an already existant property name is given this function will trigger
     * an error.
     *
     * @param string $property
     * @return integer
     */
    public function add($property)
    {
        if ($this->exists($property)) {
            trigger_error("Property $property already exists", E_USER_ERROR);
        }

        $index = empty($this->properties) ? 0 : max($this->properties) + 1;

        return ($this->properties[$property] = $index);
    }

    /**
     * Undocumented function
     *
     * @param [type] $property
     * @return void
     */
    public function get($property)
    {
        if (!$this->exists($property)) {
            return null;
        }
        return $this->properties[$property];
    }

    /**
     * Undocumented function
     *
     * @param [type] $property
     * @return void
     */
    public function addOrGet($property)
    {
        if ($this->exists($property)) {
            return $this->get($property);
        }
        return $this->add($property);
    }

    /**
     * Undocumented function
     *
     * @param [type] $property
     * @param [type] $another_property
     * @return void
     */
    public function affiliate($property, $another_property)
    {
        if ($this->exists($property)) {
            trigger_error("Property $property already exists.", E_USER_ERROR);
        }

        return ($this->properties[$property] = $this->get($another_property));
    }

    /**
     * Returns an instance of PropertiesList with the new properties integrated.
     *
     * @param array|PropertiesList $new_properties
     * @param array &$updated_properties
     * 
     * @return static
     */
    public function merge($new_properties, &$updated_properties = null)
    {
        $copy = clone $this;
        
        if ($new_properties instanceof self) {
            $new_properties = $new_properties->properties;
        }

        $copy->properties = ArrayHelper::mergeDictionaries(
            $copy->properties,
            $new_properties,
            $updated_properties
        );

        return $copy;
    }

    public function find($search_value)
    {
        return array_keys($this->properties, $search_value);
    }
}