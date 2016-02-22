<?php

namespace FlexModel\Test;

use DOMDocument;
use FlexModel\FlexModel;
use PHPUnit_Framework_TestCase;

/**
 * FlexModelTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class FlexModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * The location of cache directory used for testing.
     *
     * @var string
     */
    private $cacheDirectory;

    /**
     * The default flexmodel identifier used for testing.
     *
     * @var string
     */
    private $defaultIdentifier = 'default';

    /**
     * Creates a cache directory and removes the cache files.
     */
    public function setUp()
    {
        $this->cacheDirectory = __DIR__.'/cache';
        if (is_dir($this->cacheDirectory) === false) {
            mkdir($this->cacheDirectory);
        }

        $files = array('flexmodel-default.php', 'xinclude.xml');
        foreach ($files as $file) {
            if (file_exists($this->cacheDirectory.'/'.$file)) {
                unlink($this->cacheDirectory.'/'.$file);
            }
        }
    }

    /**
     * Removes the cache files and directory.
     */
    public function tearDown()
    {
        $this->setUp();

        rmdir($this->cacheDirectory);
    }

    /**
     * Tests if the constructing a new FlexModel instance sets the properties.
     */
    public function testConstruct()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);

        $this->assertAttributeSame('default', 'identifier', $flexModel);
    }

    /**
     * Tests if FlexModel::load successfully validates the XML in the DOMDocument.
     *
     * @depends testConstruct
     */
    public function testLoad()
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML("<flexmodel><object name='Test'></object></flexmodel>");
        $domDocument->documentURI = 'flexmodel.xml';

        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($domDocument, $this->cacheDirectory);
    }

    /**
     * Tests if FlexModel::load successfully validates the XML in the DOMDocument.
     *
     * @depends testLoad
     */
    public function testLoadWithXInclude()
    {
        $xIncludeDomDocument = new DOMDocument('1.0', 'UTF-8');
        $xIncludeDomDocument->loadXML("<object name='Test'></object>");
        $xIncludeDomDocument->save($this->cacheDirectory.'/xinclude.xml');

        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML("<flexmodel xmlns:xi='http://www.w3.org/2001/XInclude'><xi:include href='".$this->cacheDirectory."/xinclude.xml#xpointer(/object)'/></flexmodel>");
        $domDocument->documentURI = 'flexmodel.xml';
        $domDocument->xinclude();

        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($domDocument, $this->cacheDirectory);
    }

    /**
     * Tests if FlexModel::load successfully validates the XML with all posible elements in the DOMDocument.
     *
     * @depends testLoad
     */
    public function testLoadWithFullConfiguration()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);
    }

    /**
     * Tests if FlexModel::load successfully validates the XML against an extended XML Schema with the extended elements in the DOMDocument.
     *
     * @depends testLoad
     */
    public function testLoadWithExtendedSchema()
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->load(__DIR__.'/Resources/configuration/flexmodel-extended.xml');

        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($domDocument, $this->cacheDirectory, __DIR__.'/Resources/xsd/flexmodel-extended.xsd');
    }

    /**
     * Tests if FlexModel::load triggers warnings for the missing elements in the XML.
     *
     * @depends testLoad
     */
    public function testLoadWithInvalidConfiguration()
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML("<flexmodel><object></object></flexmodel>");
        $domDocument->documentURI = 'flexmodel.xml';

        $flexModel = new FlexModel($this->defaultIdentifier);

        $this->setExpectedException('PHPUnit_Framework_Error_Warning', "Line 1: Element 'object': The attribute 'name' is required but missing. in \"flexmodel.xml\"");

        $flexModel->load($domDocument, $this->cacheDirectory);
    }

    /**
     * Tests if FlexModel::load loads the generated configuration cache file.
     *
     * @depends testLoadWithFullConfiguration
     */
    public function testLoadLoadsGeneratedConfigurationCache()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertAttributeNotEmpty('configuration', $flexModel);
    }

    /**
     * Tests if FlexModel::getDOMDocument returns the loaded DOMDocument instance.
     *
     * @depends testLoad
     */
    public function testGetDOMDocument()
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML("<flexmodel><object name='Test'></object></flexmodel>");
        $domDocument->documentURI = 'flexmodel.xml';

        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($domDocument, $this->cacheDirectory);

        $this->assertSame($domDocument, $flexModel->getDOMDocument());
    }

    /**
     * Tests if FlexModel::reload reloads the (changed) configuration.
     *
     * @depends testLoad
     */
    public function testReload()
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML("<flexmodel><object name='Test'></object></flexmodel>");
        $domDocument->documentURI = 'flexmodel.xml';

        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($domDocument, $this->cacheDirectory);

        $this->assertAttributeEquals(array('Test' => array()), 'configuration', $flexModel);

        $domDocument->loadXML("<flexmodel><object name='Test'><fields><field name='datefield' datatype='DATE'/></fields></object></flexmodel>");

        $flexModel->reload();

        $this->assertAttributeEquals(array('Test' => array('fields' => array(array('name' => 'datefield', 'datatype' => 'DATE')), '__field_index' => array('datefield' => 0))), 'configuration', $flexModel);
    }

    /**
     * Tests if FlexModel::getCachePath returns null when no configuration has been loaded.
     */
    public function testGetCachePathReturnsNull()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);

        $this->assertNull($flexModel->getCachePath());
    }

    /**
     * Tests if FlexModel::getCachePath returns the location to the set cache directory.
     *
     * @depends testLoad
     * @depends testGetCachePathReturnsNull
     */
    public function testGetCachePath()
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML("<flexmodel><object name='Test'></object></flexmodel>");
        $domDocument->documentURI = 'flexmodel.xml';

        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($domDocument, $this->cacheDirectory);

        $this->assertSame($this->cacheDirectory, $flexModel->getCachePath());
    }

    /**
     * Tests if FlexModel::getCacheFile returns null when no configuration has been loaded.
     */
    public function testGetCacheFileReturnsNull()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);

        $this->assertNull($flexModel->getCacheFile());
    }

    /**
     * Tests if FlexModel::getCacheFile returns the location of the cache file.
     *
     * @depends testLoad
     * @depends testGetCacheFileReturnsNull
     */
    public function testGetCacheFile()
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML("<flexmodel><object name='Test'></object></flexmodel>");
        $domDocument->documentURI = 'flexmodel.xml';

        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($domDocument, $this->cacheDirectory);

        $this->assertSame(sprintf('%s/flexmodel-%s.php', $this->cacheDirectory, $this->defaultIdentifier), $flexModel->getCacheFile());
    }

    /**
     * Tests if FlexModel::getObjectNames returns an array with the names of the objects defined in the XML configuration.
     *
     * @depends testLoadLoadsGeneratedConfigurationCache
     */
    public function testGetObjectNames()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertSame(array('Test', 'Testforeign', 'Emptyobject'), $flexModel->getObjectNames());
    }

    /**
     * Test if FlexModel::hasObject returns false for a non-existing object.
     *
     * @depends testGetObjectNames
     */
    public function testHasObjectReturnsFalseForNonExistingObject()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertFalse($flexModel->hasObject('NonExisting'));
    }

    /**
     * Test if FlexModel::hasObject returns true for an existing object.
     *
     * @depends testHasObjectReturnsFalseForNonExistingObject
     */
    public function testHasObjectReturnsTrueForExistingObject()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertTrue($flexModel->hasObject('Test'));
    }

    /**
     * Tests if FlexModel::getModelConfiguration returns null for a non-existing object.
     *
     * @depends testLoadLoadsGeneratedConfigurationCache
     */
    public function testGetModelConfigurationReturnsNullForNonExistingObject()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertNull($flexModel->getModelConfiguration('NonExisting'));
    }

    /**
     * Tests if FlexModel::getModelConfiguration returns an array with the configuration of the object.
     *
     * @depends testGetModelConfigurationReturnsNullForNonExistingObject
     */
    public function testGetModelConfigurationReturnsArrayForExistingObject()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getModelConfiguration('Test'));
    }

    /**
     * Tests if FlexModel::getModelConfiguration returns an empty array with a fields key within the configuration of the object without fields.
     *
     * @depends testGetModelConfigurationReturnsArrayForExistingObject
     */
    public function testGetModelConfigurationReturnsArrayForExistingObjectWithoutFields()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getModelConfiguration('Testforeign'));
        $this->assertArrayHasKey('fields', $flexModel->getModelConfiguration('Emptyobject'));
    }

    /**
     * Tests if FlexModel::getField returns null for a non-existing field.
     *
     * @depends testGetModelConfigurationReturnsArrayForExistingObject
     */
    public function testGetFieldReturnsNullForNonExistingField()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertNull($flexModel->getField('Test', 'non_existing'));
    }

    /**
     * Tests if FlexModel::getField returns an array with the configuration of the field.
     *
     * @depends testGetFieldReturnsNullForNonExistingField
     */
    public function testGetFieldReturnsArrayForExistingField()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getField('Test', 'booleanfield'));
    }

    /**
     * Tests if FlexModel::getField returns an array with the configuration of the field without form defaults.
     *
     * @depends testGetFieldReturnsArrayForExistingField
     */
    public function testGetFieldReturnsArrayForExistingFieldWithoutFormDefaults()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getField('Test', 'varcharfield', true));
        $this->assertArrayNotHasKey('form_defaults', $flexModel->getField('Test', 'varcharfield', true));
    }

    /**
     * Tests if FlexModel::hasField returns false for a non-existing field.
     *
     * @depends testGetFieldReturnsNullForNonExistingField
     */
    public function testHasFieldReturnsFalseForNonExistingField()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertFalse($flexModel->hasField('Test', 'non_existing'));
    }

    /**
     * Tests if FlexModel::hasField returns true for an existing field.
     *
     * @depends testGetFieldReturnsArrayForExistingField
     */
    public function testHasFieldReturnsTrueForExistingField()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertTrue($flexModel->hasField('Test', 'booleanfield'));
    }

    /**
     * Tests if FlexModel::addExternalObjectReference adds an external object to the externalObjectReferences property of the FlexModel instance.
     */
    public function testAddExternalObjectReference()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->addExternalObjectReference('ExternalObject');

        $this->assertAttributeSame(array('ExternalObject'), 'externalObjectReferences', $flexModel);
    }

    /**
     * Tests if FlexModel::isObjectReference returns false for a non-existing object.
     *
     * @depends testHasObjectReturnsTrueForExistingObject
     */
    public function testIsObjectReferenceReturnsFalseForNonExistingObject()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertFalse($flexModel->isObjectReference('NonExisting'));
    }

    /**
     * Tests if FlexModel::isObjectReference returns true for an existing object.
     *
     * @depends testIsObjectReferenceReturnsFalseForNonExistingObject
     */
    public function testIsObjectReferenceReturnsTrueForExistingObject()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertFalse($flexModel->isObjectReference('NonExisting'));
    }

    /**
     * Tests if FlexModel::isObjectReference returns true for an existing object outside of the flexmodel configuration.
     *
     * @depends testIsObjectReferenceReturnsTrueForExistingObject
     * @depends testAddExternalObjectReference
     */
    public function testIsObjectReferenceReturnsTrueForExternalObject()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);
        $flexModel->addExternalObjectReference('ExternalObject');

        $this->assertTrue($flexModel->isObjectReference('ExternalObject'));
    }

    /**
     * Tests if FlexModel::getFormConfiguration returns null for a non-existing form.
     */
    public function testGetFormConfigurationReturnsNullForNonExistingForm()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertNull($flexModel->getFormConfiguration('Test', 'non-existing'));
    }

    /**
     * Tests if FlexModel::getFormConfiguration returns an array with the form configuration for an existing form.
     *
     * @depends testGetFormConfigurationReturnsNullForNonExistingForm
     */
    public function testGetFormConfigurationReturnsArrayForExistingForm()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getFormConfiguration('Test', ''));
    }

    /**
     * Tests if FlexModel::getViewConfiguration returns null for a non-existing view.
     */
    public function testGetViewConfigurationReturnsNullForNonExistingView()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertNull($flexModel->getViewConfiguration('Test', 'non-existing'));
    }

    /**
     * Tests if FlexModel::getViewConfiguration returns an array with the view configuration for an existing view.
     *
     * @depends testGetViewConfigurationReturnsNullForNonExistingView
     */
    public function testGetViewConfigurationReturnsArrayForExistingView()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getViewConfiguration('Test', 'overview'));
    }

    /**
     * Tests if FlexModel::getModelConfiguration returns an empty array with a fields key within the configuration of the object without fields.
     *
     * @depends testGetViewConfigurationReturnsArrayForExistingView
     */
    public function testGetViewConfigurationReturnsArrayForExistingViewWithoutFields()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getViewConfiguration('Test', 'empty'));
        $this->assertArrayHasKey('fields', $flexModel->getViewConfiguration('Test', 'empty'));
    }

    /**
     * Tests if FlexModel::getViewConfigurationsByViewgroupOfView returns an empty array when the specified view is not in a viewgroup.
     *
     * @depends testLoadLoadsGeneratedConfigurationCache
     */
    public function testGetViewConfigurationsByViewgroupOfViewReturnsEmptyArrayWhenViewNotInViewgroup()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getViewConfigurationsByViewgroupOfView('Test', 'overview'));
        $this->assertEmpty($flexModel->getViewConfigurationsByViewgroupOfView('Test', 'overview'));
    }

    /**
     * Tests if FlexModel::getViewConfigurationsByViewgroupOfView returns an array with all views within the viewgroup of the specified view.
     *
     * @depends testGetViewConfigurationsByViewgroupOfViewReturnsEmptyArrayWhenViewNotInViewgroup
     */
    public function testGetViewConfigurationsByViewgroupOfViewReturnsArrayWithAllViewsInViewgroup()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getViewConfigurationsByViewgroupOfView('Test', 'view-in-viewgroup'));
        $this->assertArrayHasKey('view-in-viewgroup', $flexModel->getViewConfigurationsByViewgroupOfView('Test', 'view-in-viewgroup'));
        $this->assertArrayHasKey('view-in-viewgroup2', $flexModel->getViewConfigurationsByViewgroupOfView('Test', 'view-in-viewgroup'));
    }

    /**
     * Tests if FlexModel::getFieldNames returns an array with all the fields in the specified object.
     *
     * @depends testLoadLoadsGeneratedConfigurationCache
     */
    public function testGetFieldNames()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getFieldNames('Test'));
        $this->assertNotEmpty($flexModel->getFieldNames('Test'));
    }

    /**
     * Tests if FlexModel::getFieldsByView returns an array with all the field configurations within the specified view of an object.
     *
     * @depends testLoadLoadsGeneratedConfigurationCache
     */
    public function testGetFieldsByView()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getFieldsByView('Test', 'overview'));
        $this->assertNotEmpty($flexModel->getFieldsByView('Test', 'overview'));
    }

    /**
     * Tests if FlexModel::getFieldNamesByView returns an array with all the field names within the specified view of an object.
     *
     * @depends testGetFieldsByView
     */
    public function testGetFieldNamesByView()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getFieldNamesByView('Test', 'overview'));
        $this->assertContains('booleanfield', $flexModel->getFieldNamesByView('Test', 'overview'));
    }

    /**
     * Tests if FlexModel::getFields returns an array with all the field configurations of the specified object.
     *
     * @depends testLoadLoadsGeneratedConfigurationCache
     */
    public function testGetFields()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getFields('Test'));
        $this->assertNotEmpty($flexModel->getFields('Test'));
    }

    /**
     * Tests if FlexModel::getFieldsByDatatype returns an array with all the field configurations of the specified datatype for an object.
     *
     * @depends testGetFields
     */
    public function testGetFieldsByDatatype()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertInternalType('array', $flexModel->getFieldsByDatatype('Test', 'VARCHAR'));
        $this->assertCount(2, $flexModel->getFieldsByDatatype('Test', 'VARCHAR'));
    }

    /**
     * Tests if FlexModel::getReferencedField returns null for a non-existing reference field to another object.
     *
     * @depends testGetFieldReturnsArrayForExistingField
     */
    public function testGetReferencedFieldReturnsNullForNonExistingObjectReferenceField()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $this->assertNull($flexModel->getReferencedField('Test', 'testforeign_non_existing_reference_varcharfield'));
    }

    /**
     * Tests if FlexModel::getReferencedField returns an array of the field configuration that is a reference to another object.
     *
     * @depends testGetReferencedFieldReturnsNullForNonExistingObjectReferenceField
     */
    public function testGetReferencedField()
    {
        $flexModel = new FlexModel($this->defaultIdentifier);
        $flexModel->load($this->loadFlexModelTestFile(), $this->cacheDirectory);

        $excludedFieldNameParts = array();
        $this->assertInternalType('array', $flexModel->getReferencedField('Test', 'testforeign_reference_varcharfield', $excludedFieldNameParts));
        $this->assertSame(array('varcharfield'), $excludedFieldNameParts);
    }

    /**
     * Tests if FlexModel::sortByLocation returns the expected result based on the field arrays.
     *
     * @dataProvider provideTestSortByLocation
     *
     * @param array $field1
     * @param array $field2
     * @param bool  $expectedResult
     */
    public function testSortByLocation(array $field1, array $field2, $expectedResult)
    {
        $flexModel = new FlexModel();

        $this->assertEquals($expectedResult, $flexModel->sortByLocation($field1, $field2));
    }

    /**
     * Returns an array with testcases for @see testSortByLocation.
     *
     * @return array
     */
    public function provideTestSortByLocation()
    {
        return array(
            array(array('location' => 1), array('location' => 2), -1),
            array(array('location' => 2), array('location' => 1), 1),
            array(array('location' => 1), array('location' => 1), 0),
        );
    }

    /**
     * Tests if FlexModel::isQuotedValue returns the expected result based on the value.
     *
     * @dataProvider provideTestIsQuotedValue
     *
     * @param string $value
     * @param bool   $expectedResult
     */
    public function testIsQuotedValue($value, $expectedResult)
    {
        $this->assertEquals($expectedResult, FlexModel::isQuotedValue($value));
    }

    /**
     * Returns an array with testcases for @see testIsQuotedValue.
     *
     * @return array
     */
    public function provideTestIsQuotedValue()
    {
        return array(
            array('string', true),
            array('1', false),
            array('true', false),
            array('false', false),
            array('null', false),
        );
    }

    /**
     * Returns a new DOMDocument instance with a flexmodel file loaded.
     *
     * @return DOMDocument
     */
    private function loadFlexModelTestFile()
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domDocument->load(__DIR__.'/Resources/configuration/flexmodel.xml');

        return $domDocument;
    }
}
