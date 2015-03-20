<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\IItem;

/**
 * Data model class for DC_General <-> MetaModel adaption.
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class Model implements ModelInterface
{

    /**
     * The MetaModel item accessible via this instance.
     *
     * @var IItem
     */
    protected $objItem = null;

    /**
     * The meta information the DC and views need to buffer in this model.
     *
     * @var array
     */
    protected $arrMetaInformation = array();

    /**
     * Return the names of all properties stored within this model.
     *
     * @return string[]
     */
    protected function getPropertyNames()
    {
        $propertyNames = array('id', 'pid', 'tstamp', 'sorting');

        if ($this->getItem()->getMetaModel()->hasVariants()) {
            $propertyNames[] = 'varbase';
            $propertyNames[] = 'vargroup';
        }

        return array_merge($propertyNames, array_keys($this->getItem()->getMetaModel()->getAttributes()));
    }

    /**
     * Returns the native IMetaModelItem instance encapsulated within this abstraction.
     *
     * @return IItem
     */
    public function getItem()
    {
        return $this->objItem;
    }

    /**
     * Create a new instance of this class.
     *
     * @param IItem $objItem The item that shall be encapsulated.
     */
    public function __construct($objItem)
    {
        $this->objItem = $objItem;
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        $this->objItem = $this->getItem()->copy();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getItem()->get('id');
    }

    /**
     * {@inheritDoc}
     */
    public function getProperty($strPropertyName)
    {
        if ($this->getItem()) {
            $varValue = $this->getItem()->get($strPropertyName);
            // Test if it is an attribute, if so, let it transform the data for the widget.
            $objAttribute = $this->getItem()->getAttribute($strPropertyName);
            if ($objAttribute) {
                $varValue = $objAttribute->valueToWidget($varValue);
            }
            return $varValue;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertiesAsArray()
    {
        $arrResult = array();

        foreach ($this->getPropertyNames() as $strKey) {
            $arrResult[$strKey] = $this->getProperty($strKey);
        }
        return $arrResult;
    }

    /**
     * {@inheritDoc}
     */
    public function getMeta($strMetaName)
    {
        if (array_key_exists($strMetaName, $this->arrMetaInformation)) {
            return $this->arrMetaInformation[$strMetaName];
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setId($mixID)
    {
        if ($this->getId() == null) {
            $this->getItem()->set('id', $mixID);
            $this->setMeta(static::IS_CHANGED, true);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws \LogicException When the property is unable to accept the value.
     */
    public function setProperty($strPropertyName, $varValue)
    {
        if ($this->getItem()) {
            $varInternalValue = $varValue;
            // Test if it is an attribute, if so, let it transform the data for the widget.
            $objAttribute = $this->getItem()->getAttribute($strPropertyName);
            if ($objAttribute) {
                $varInternalValue = $objAttribute->widgetToValue($varValue, $this->getItem()->get('id'));
            }

            if ($varValue !== $this->getProperty($strPropertyName)) {
                $this->setMeta(static::IS_CHANGED, true);
                $this->getItem()->set($strPropertyName, $varInternalValue);
                if ($varValue !== $this->getProperty($strPropertyName)) {
                    throw new \LogicException(
                        sprintf(
                            'Property %s (%s) did not accept the value %s.',
                            $strPropertyName,
                            $objAttribute->get('type'),
                            var_export($varValue, true)
                        )
                    );
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPropertiesAsArray($arrProperties)
    {
        foreach ($arrProperties as $strKey => $varValue) {
            $this->setProperty($strKey, $varValue);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setMeta($strMetaName, $varValue)
    {
        $this->arrMetaInformation[$strMetaName] = $varValue;
    }

    /**
     * {@inheritDoc}
     */
    public function hasProperties()
    {
        return ($this->getItem()) ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ModelIterator($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName()
    {
        return $this->getItem()->getMetaModel()->getTableName();
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When a property in the value bag has been marked as invalid.
     */
    public function readFromPropertyValueBag(PropertyValueBagInterface $valueBag)
    {
        foreach ($this->getPropertyNames() as $property) {
            if (!$valueBag->hasPropertyValue($property)) {
                continue;
            }

            if ($valueBag->isPropertyValueInvalid($property)) {
                throw new DcGeneralInvalidArgumentException('The value for property ' . $property . ' is invalid.');
            }

            $this->setProperty($property, $valueBag->getPropertyValue($property));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function writeToPropertyValueBag(PropertyValueBagInterface $valueBag)
    {
        foreach ($this->getPropertyNames() as $property) {
            if (!$valueBag->hasPropertyValue($property)) {
                continue;
            }

            $valueBag->setPropertyValue($property, $this->getProperty($property));
        }
    }
}
