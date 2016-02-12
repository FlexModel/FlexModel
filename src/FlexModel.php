<?php

namespace FlexModel;

/**
 * FlexModel.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class FlexModel
{
    /**
     * Loads the XML configuration file.
     *
     * @param string      $file
     * @param string      $cachePath
     * @param string|null $schema
     */
    public function load($file, $cachePath, $schema = null)
    {
    }

    /**
     * Reloads the cached configuration.
     */
    public function reload()
    {
    }

    /**
     * Returns true when the configuration contains an object with the specified object name.
     *
     * @param string $objectName
     *
     * @return bool
     */
    public function has($objectName)
    {
    }

    /**
     * Returns true when the configuration of an object contains the specified field name.
     *
     * @param string $objectName
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasField($objectName, $fieldName)
    {
    }

    /**
     * Returns true when the datatype is a valid object reference.
     *
     * @param string $datatype
     *
     * @return bool
     */
    public function isObjectReference($datatype)
    {
    }

    /**
     * Returns the path to the cache directory.
     */
    public function getCachePath()
    {
    }

    /**
     * Returns all configured object names.
     *
     * @return array
     */
    public function getObjectNames()
    {
    }

    /**
     * Returns the model for the specified object.
     *
     * @param string $objectName
     *
     * @return array
     */
    public function getModelConfiguration($objectName)
    {
    }

    /**
     * Returns field configuration of the specified form in the object.
     *
     * @param string $objectName
     * @param string $formName
     *
     * @return array
     */
    public function getFormConfiguration($objectName, $formName)
    {
    }

    /**
     * Returns field configuration of the specified view in the object.
     *
     * @param string $objectName
     * @param string $viewName
     *
     * @return array
     */
    public function getViewConfiguration($objectName, $viewName)
    {
    }

    /**
     * Returns all the field configurations of views in the viewgroup of the specified view name.
     *
     * @param string      $objectName
     * @param string      $viewName
     *
     * @return array
     */
    public function getViewConfigurationsByViewgroupOfView($objectName, $viewName)
    {
    }

    /**
     * Returns the field names of the specified object.
     *
     * @return array
     */
    public function getFieldNames($objectName)
    {
    }

    /**
     * Returns the field names of the specified view in the object.
     *
     * @param string $objectName
     * @param string $viewName
     *
     * @return array
     */
    public function getFieldNamesByView($objectName, $viewName)
    {
    }

    /**
     * Returns all fields for the object.
     *
     * @param string $objectName
     *
     * @return array
     */
    public function getFields($objectName)
    {
    }

    /**
     * Returns all fields with the specified datatype for an object.
     *
     * @param string $objectName
     * @param string $datatype
     *
     * @return array
     */
    public function getFieldsByDatatype($objectName, $datatype)
    {
    }

    /**
     * Returns the fields in the object view.
     *
     * @param string $objectName
     * @param string $viewName
     * @param bool   $sortByLocationOddEven
     *
     * @return array
     */
    public function getFieldsByView($objectName, $viewName, $sortByLocationOddEven = false)
    {
    }

    /**
     * Returns the configuration of a field in the specified object.
     *
     * @param string $objectName
     * @param string $fieldName
     *
     * @return array|null
     */
    public function getField($objectName, $fieldName)
    {
    }

    /**
     *
     * @param string $objectName
     * @param string $fieldName
     * @param array  $excludedFieldNameParts
     * @param bool   $objectReferencesOnly
     *
     * @return array|null
     */
    public function getReferencedField($objectName, $fieldName, array & $excludedFieldNameParts = array(), $objectReferencesOnly = true)
    {
    }

    /**
     * Adds an object name which is a valid datatype reference outside of the configuration.
     *
     * @param string $objectName
     */
    public function addObjectReference($objectName)
    {
    }

    /**
     * Sorts field configuration array by location.
     *
     * @param array $field1
     * @param array $field2
     *
     * @return int
     */
    public function sortByLocation(array $field1, array $field2)
    {
    }

    /**
     * Sorts field configuration array by location modulo (odd / even) and location.
     *
     * @param array $field1
     * @param array $field2
     *
     * @return int
     */
    public function sortByLocationOddEven(array $field1, array $field2)
    {
    }
}
