<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use DcGeneral\DC_General;
use MetaModels\IMetaModel;

use MetaModels\Factory as ModelFactory;

/**
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class RenderSettings extends Helper
{

	/**
	 * @var RenderSettings
	 */
	protected static $objInstance = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return RenderSettings
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null)
		{
			self::$objInstance = new RenderSettings();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
		parent::__construct();
	}

	public function drawSetting($arrRow, $strLabel = '', DC_General $objDC = null, $imageAttribute = '', $blnReturnImage = false, $blnProtected = false)
	{
		return $strLabel . ($arrRow['isdefault'] ? ' <span style="color:#b3b3b3; padding-left:3px">[' . $GLOBALS['TL_LANG']['MSC']['fallback'] . ']</span>' : '');
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param \DCGeneral\DC_General $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	public function getTemplates(DC_General $objDC)
	{
		return $this->getTemplatesForBase('metamodel_');
	}

	public function getFilterSettings($objMCW)
	{
		$objModel = $this->Database->prepare('SELECT pid FROM tl_metamodel_rendersettings WHERE id = ?')->execute($objMCW->currentRecord);
		$objFilters = $this->Database->prepare('SELECT id, name FROM tl_metamodel_filter WHERE pid = ?')->execute($objModel->pid);
		$arrResult = array();
		while ($objFilters->next())
		{
			$arrResult[$objFilters->id] = $objFilters->name;
		}
		return $arrResult;
	}

	/**
	 * create an empty lang array if no data is given
	 * @param type $varValue
	 * @return type
	 */
	public function prepareMCW($varValue)
	{
		$varValue = deserialize($varValue, true);
		$newValues = array();
		$arrLanguages = $GLOBALS['TL_DCA']['tl_metamodel_rendersettings']['fields']['jumpTo']['eval']['columnFields']['langcode']['options'];

		foreach ($arrLanguages as $key => $lang)
		{
			$newValue = '';

			//search for existing values
			if ($varValue)
			{
				foreach ($varValue as $k => $arr)
				{
					if (!is_array($arr)) break;
					//set the new value and exit the loop
					if (array_search($key, $arr) !==false)
					{
						$newValue = '{{link_url::'.$arr['value'].'}}';
						$intFilter = $arr['filter'];
						break;
					}
				}
			}

			//build the new array
			$newValues[] = array(
				'langcode' => $key,
				'value' => $newValue,
				'filter' => $intFilter
			);
		}
		return serialize($newValues);
	}

	public function saveMCW($varValue)
	{
		$varValue = deserialize($varValue, true);
		foreach ($varValue as $k => $v)
		{
			$varValue[$k]['value'] = str_replace(array('{{link_url::', '}}'), array('',''),$v['value']);
		}

		return serialize($varValue);
	}

	/**
	 *
	 * @param type $objDC
	 * @return type
	 * @throws Exception
	 */
	public function onLoadCallback($objDC)
	{
		// do nothing if not in edit/create mode.
		if(!(($this->Input->get('pid') || $this->Input->get('id')) && in_array($this->Input->get('act'), array('create', 'edit'))))
		{
			return;
		}

		if ($objDC->id)
		{
			$objMetaModel = ModelFactory::byId(
				$this->Database->prepare('SELECT pid FROM tl_metamodel_rendersettings WHERE id = ?')
					->execute($objDC->id)
					->pid
			);
		} else if ($this->Input->get('pid')) {
			$objMetaModel = ModelFactory::byId($this->Input->get('pid'));
		}

		if (!$objMetaModel)
		{
			throw new \RuntimeException('unexpected condition, metamodel unknown', 1);
		}


		$this->prepareJumpToMcw($objMetaModel);
	}



	protected function prepareJumpToMcw(IMetaModel $objMetaModel)
	{
		$arrwidget = array();
		if($objMetaModel->isTranslated())
		{
			$this->loadLanguageFile('languages');
			$arrLanguages = array();
			foreach((array)$objMetaModel->getAvailableLanguages() as $strLangCode)
			{
				$arrLanguages[$strLangCode] = $GLOBALS['TL_LANG']['LNG'][$strLangCode];
			}
			asort($arrLanguages);

			$GLOBALS['TL_DCA']['tl_metamodel_rendersettings']['fields']['jumpTo']['minCount'] = count($arrLanguages);
			$GLOBALS['TL_DCA']['tl_metamodel_rendersettings']['fields']['jumpTo']['maxCount'] = count($arrLanguages);
			$GLOBALS['TL_DCA']['tl_metamodel_rendersettings']['fields']['jumpTo']['eval']['columnFields']['langcode']['options'] = $arrLanguages;
		}
		else
		{
			$GLOBALS['TL_DCA']['tl_metamodel_rendersettings']['fields']['jumpTo']['minCount'] = 1;
			$GLOBALS['TL_DCA']['tl_metamodel_rendersettings']['fields']['jumpTo']['maxCount'] = 1;
			$GLOBALS['TL_DCA']['tl_metamodel_rendersettings']['fields']['jumpTo']['eval']['columnFields']['langcode']['options'] =
				array('xx' => $GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_allLanguages']);
		}

	}

	/**
	 * Get a list with all CSS files inside of the tl_files.
	 *
	 * @return array
	 */
	public function getCssFiles()
	{
		$arrCssFiles = array();

		$this->searchFiles($GLOBALS['TL_CONFIG']['uploadPath'], $arrCssFiles, ".css");

		return $arrCssFiles;
	}

	/**
	 * Get a list with all JS files inside of the tl_files.
	 *
	 * @return array
	 */
	public function getJsFiles()
	{
		$arrJsFiles = array();

		$this->searchFiles($GLOBALS['TL_CONFIG']['uploadPath'], $arrJsFiles, ".js");

		return $arrJsFiles;
	}

	protected function searchFiles($strFolder, &$arrResult, $strExtension)
	{
		// Check if we have a file or folder.
		if(!is_file(TL_ROOT . '/' . $strFolder) && file_exists(TL_ROOT . '/' . $strFolder))
		{
			$arrScanResult = scan(TL_ROOT . '/' . $strFolder);
		}
		else if(is_file(TL_ROOT . '/' . $strFolder) && file_exists(TL_ROOT . '/' . $strFolder))
		{
			$arrScanResult = array();
		}

		// Run each value.
		foreach ($arrScanResult as $key => $value)
		{
			if(!is_file(TL_ROOT . '/' . $strFolder . '/' . $value))
			{
				$this->searchFiles($strFolder . '/' . $value, $arrResult, $strExtension);
			}
			else
			{
				if(preg_match('/'.$strExtension.'$/i', $value))
				{
					$arrResult[$strFolder][$strFolder . '/' . $value] = $value;
				}
			}
		}
	}

	/**
	 * Make sure there is only one default per mm
	 *
	 * @param string                $varValue The value, either '1' or ''.
	 *
	 * @param \DcGeneral\DC_General $dc
	 *
	 * @return mixed
	 */
	public function checkDefault($varValue, DC_General $dc)
	{
		if ($varValue == '')
		{
			return '';
		}

		// Get Parent MM
		$intParentMm = null;
		if ($dc->id)
		{
			// Get current row.
			$objRendersettings = $this->Database
				->prepare('SELECT id, pid
						FROM tl_metamodel_rendersettings
						WHERE id=?')
				->execute($dc->id);

			if ($objRendersettings->numRows == 0)
			{
				return '';
			}

			// Get all siblings
			$intParentMm = $objRendersettings->pid;
		}
		else if ($this->Input->get('pid'))
		{
			$intParentMm = $this->Input->get('pid');
		}
		else
		{
			return '';
		}

		$objSiblingRendersettings = $this->Database
			->prepare('SELECT id
					FROM tl_metamodel_rendersettings
					WHERE pid=?
						AND isdefault=1')
			->execute($intParentMm);

		// Check if we have some.
		if ($objSiblingRendersettings->numRows == 0)
		{
			return $varValue;
		}

		// Reset all default flags.
		$arrSiblings = $objSiblingRendersettings->fetchEach('id');
		$arrSiblings = array_map('intval', $arrSiblings);

		$this->Database
			->prepare('UPDATE tl_metamodel_rendersettings
					SET isdefault = ""
					WHERE id IN(' . implode(', ', $arrSiblings) . ')
						AND isdefault=1')
			->execute();

		return $varValue;
	}

	/**
	 * Return the link picker wizard
	 * @param DataContainer
	 * @return string
	 */
	public function pagePicker(\DataContainer $dc)
	{
		if(version_compare(VERSION, '3.0', '<'))
		{
			return ' ' . $this->generateImage('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer" onclick="Backend.pickPage(\'ctrl_' . $dc->inputName . '\')"');
		}
		else
		{
			$strUrl = \Environment::get('base') . 'contao/page.php?do=metamodels&table=tl_metamodel_rendersettings&field=ctrl_' . $dc->inputName;
			$strOptions = "{'width':765,'title':'Seitenstruktur','url':'" . $strUrl . "','id':'" . $dc->inputName . "','tag':'ctrl_" . $dc->inputName . "','self':this}";
					
			return ' ' . $this->generateImage(
				'pickpage.gif', 
				$GLOBALS['TL_LANG']['MSC']['pagepicker'], 
				'style="vertical-align:top;cursor:pointer" onclick="Backend.openModalSelector(' . $strOptions . ')"');
		}
	}
}

