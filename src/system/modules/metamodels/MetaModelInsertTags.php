<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package     MetaModels
 * @subpackage  metamodels_inserttags
 * @author      Tim Gatzky <info@tim-gatzky.de>
 * @author		Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */
/*
  Available inserttags

  {{metamodel::total::list::ce::*}}
  {{metamodel::total::list::mod::*}}
  {{metamodel::total::filter::ce::*}}
  {{metamodel::total::filter::mod::*}}
  --> no need to tell the inserttag its a list or filter type
  {{metamodel::total::mod::*}}
  {{metamodel::total::ce::*}}

  -- item based
  {{metamodelitem::table-or-id::item-or-setOfIds::rendersetting}} -> html output
  {{metamodeldetailitem::table-or-id::item::rendersetting}} -> html output

  -- attribute based
  {{metamodelattribute::table-or-id::item-id::field}} -> value, plain text

  --------------------------------------------------

 */

/**
 * MetaModelsInserttag.
 * 
 * -- Total Count --
 * mm::total::mod::[id]
 * mm::total::ce::[id]
 * 
 * -- Item --
 * mm::item::[MM Name|ID]::[Item ID|ID,ID,ID]::[ID rendersetting](::[Output raw|text|html|..])
 * mm::detail::[MM Name|ID]::[Item ID]::[ID rendersetting](::[Output raw|text|html|..])
 * 
 * -- Atrribute --
 * mm::attribute::[MM Name|ID]::[Item ID]::[Attribute Name|ID](::[Output raw|text|html|..])
 * 
 * -- JumpTo --
 * mm::jumpTo::[MM Name|ID]::[Item ID]::[ID rendersetting]
 */
class MetaModelInsertTags extends Controller
{

	public function replaceTags($strTag)
	{
		$arrElements = explode('::', $strTag);

		// Check if we have the mm tags.
		if ($arrElements[0] != 'mm')
		{
			return false;
		}
		
		try
		{
			// Call the fitting function.
			switch ($arrElements[1])
			{
				// Count for mod or ce elements.
				case 'total':
					return $this->getCount($arrElements[2], $arrElements[3]);

				// Get value from an attribute.
				case 'attribute':
					return $this->getAttribute($arrElements[2], $arrElements[3], $arrElements[4], $arrElements[5]);

				// Get item.
				case 'item':
					return $this->getItem($arrElements[2], $arrElements[3], $arrElements[4]);
			}
		}
		catch (Exception $exc)
		{
			$this->log('Error by replac tags: ' . $exc->getMessage(), __CLASS__ . ' | ' . __FUNCTION__, TL_ERROR);
		}
		
		return false;
	}

	////////////////////////////////////////////////////////////////////////////
	// Tag functions
	////////////////////////////////////////////////////////////////////////////

	/**
	 * Get an item.
	 * 
	 * @param type $mixMMName
	 * @param type $mixDataId
	 * @param type $idRendesetting
	 * @param type $strOutput ToDo: Add function
	 * 
	 * @return boolean|string
	 */
	protected function getItem($mixMMName, $mixDataId, $intIdRendesetting, $strOutput = 'raw')
	{
		// Get the MetaModel. Return if we can not find one.
		$objMetaModel = $this->loadMM($mixMMName);
		if ($objMetaModel == null)
		{
			return false;
		}
		
		// Set output to default if not set.
		if(empty($strOutput))
		{
			$strOutput = 'raw';
		}

		$objMetaModelList = new MetaModelList();
		$objMetaModelList->setMetaModel($objMetaModel->get('id'), $intIdRendesetting);

		// handle a set of ids
		$arrIds = trimsplit(',', $mixDataId);

		// Check each id if published.
		foreach ($arrIds as $intKey => $intId)
		{
			if (!$this->isPublishedItem($objMetaModel, $intId))
			{
				unset($arrIds[$intKey]);
			}
		}

		// Render an empty inserttag rather than displaying a list with an empty 
		// result information. do not return false here because the inserttag itself is correct.
		if (count($arrIds) < 1)
		{
			return '';
		}

		$objMetaModelList->addFilterRule(new MetaModelFilterRuleStaticIdList($arrIds));
		return $objMetaModelList->render(false, $this);
	}

	/**
	 * Get from MM X the item with the id Y and parse the attribute Z and
	 * return it.
	 * 
	 * @param mixed $mixMMName Name or id of mm.
	 * @param type $intDataId ID form the row.
	 * @param type $strAttributeName Name of the attribute.
	 * @param string $strOutput Output format
	 * 
	 * @return boolean|mixed
	 */
	protected function getAttribute($mixMMName, $intDataId, $strAttributeName, $strOutput = 'raw')
	{
		// Get the MM.
		$objMM = $this->loadMM($mixMMName);
		if ($objMM == null)
		{
			return false;
		}		
		
		// Set output to default if not set.
		if(empty($strOutput))
		{
			$strOutput = 'raw';
		}

		// Get item.
		$objMetaModelItem = $objMM->findById($intDataId);
		
		// Parse attribute.
		$arrAttr = $objMetaModelItem->parseAttribute($strAttributeName);

		// ToDo: Maybe this should not allways be a text element.
		return $arrAttr[$strOutput];
	}

	/**
	 * Get count from a module or content element of a mm.
	 * 
	 * @param string $strType Type of element like mod or ce.
	 * @param type $intID
	 * 
	 * @return boolean
	 */
	protected function getCount($strType, $intID)
	{
		switch ($strType)
		{
			// From module, can be a metamodel list or filter
			case 'mod':
				$objMMResult = $this->getMMDataFrom('tl_module', $intID);
				break;

			// From content element, can be a metamodel list or filter.
			case 'ce':
				$objMMResult = $this->getMMDataFrom('tl_content', $intID);
				break;

			// Unknow element type.
			default:
				return false;
		}

		// Check if we have data
		if ($objMMResult != null)
		{
			return $this->getCountFor($objMMResult->metamodel, $objMMResult->metamodel_filtering);
		}

		return false;
	}

	////////////////////////////////////////////////////////////////////////////
	// Helper
	////////////////////////////////////////////////////////////////////////////

	/**
	 * Try to laod the mm by id or name.
	 * 
	 * @param mixed $mixMMName Name or id of mm.
	 * 
	 * @return IMetaModel|null
	 */
	protected function loadMM($mixMMName)
	{
		// ID.
		if (is_numeric($mixMMName))
		{
			return MetaModelFactory::byId($mixMMName);
		}
		// Name.
		else if (is_string($mixMMName))
		{
			return MetaModelFactory::byTableName($mixMMName);
		}

		// Unknown.
		return null;
	}

	/**
	 * Get some informations about a table.
	 * 
	 * @param string $strTable Name of table
	 * @return null
	 */
	protected function getMMDataFrom($strTable, $intID)
	{
		$objDB = Database::getInstance();

		// Check if we know the table
		if (!$objDB->tableExists($strTable))
		{
			return null;
		}

		// Get all information form table or retunr null if we have no data.
		$objResult = $objDB
				->prepare("SELECT metamodel, metamodel_filtering FROM " . $strTable . " WHERE id=?")
				->limit(1)
				->execute($intID);

		// Check if we have some data.
		if ($objResult->numRows < 1)
		{
			return null;
		}

		return $objResult;
	}

	/**
	 * Get count form one MM for chosen filter.
	 * 
	 * @param int $intMMId ID of the metamodels
	 * @param int $intFilterID ID of the filter
	 * 
	 * @return boolean|int False for no data or integer for the count result.
	 */
	protected function getCountFor($intMMId, $intFilterID)
	{
		// ToDo: Add check if we have realy a mm and ff.
		$objMetaModel = $this->loadMM($intMMId);
		if ($objMetaModel == null)
		{
			return false;
		}

		$objFilter = $objMetaModel->prepareFilter($intFilterID, $_GET);

		return $objMetaModel->getCount($objFilter);
	}

	/**
	 * Check if the item is published.
	 * 
	 * @param IMetaModel $objMetaModel
	 * @param int $intItemId
	 * 
	 * @return boolean
	 */
	protected function isPublishedItem($objMetaModel, $intItemId)
	{
		// check publish state of item
		$objAttrCheckPublish = Database::getInstance()
				->prepare("SELECT colname FROM tl_metamodel_attribute WHERE pid=? AND check_publish=1")
				->limit(1)
				->execute($objMetaModel->get('id'));

		if ($objAttrCheckPublish->numRows > 0)
		{
			$objItem = $objMetaModel->findById($intItemId);
			if (!$objItem->get($objAttrCheckPublish->colname))
			{
				return false;
			}
		}

		return true;
	}

}