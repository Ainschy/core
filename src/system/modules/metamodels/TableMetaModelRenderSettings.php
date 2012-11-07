<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class TableMetaModelRenderSettings extends Backend
{

	/**
	 * @var TableMetaModelRenderSetting
	 */
	protected static $objInstance = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return MetaPalettes
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null)
		{
			self::$objInstance = new TableMetaModelRenderSettings();
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

	public function drawSetting($arrRow, $strLabel = '', DataContainer $objDC = null, $imageAttribute = '', $blnReturnImage = false, $blnProtected = false)
	{
		return $strLabel . ($arrRow['isdefault'] ? ' <span style="color:#b3b3b3; padding-left:3px">[' . $GLOBALS['TL_LANG']['MSC']['fallback'] . ']</span>' : '');
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	public function getTemplates(DataContainer $objDC)
	{
		return $this->getTemplateGroup('metamodel_');
	}
	
	
	/**
	 * 
	 * @param DataContainer $objDC
	 * @return type
	 */
	public function getLanguages(MultiColumnWizard $objMCW)
	{
		$objLangs = $this->Database->prepare('SELECT pid FROM tl_metamodel_rendersettings WHERE id = ?')->execute($objMCW->currentRecord);
		$objMetaModel = MetaModelFactory::byId($objLangs->pid);
		return $objMetaModel->getAvailableLanguages();
	}
	
	/**
	 * create an empty lang array if no data is given
	 * @param type $varValue
	 * @return type
	 */
	public function prepareMCW($varValue)
	{
		$varValue = deserialize($varValue, true);
		if (!$varValue)
		{
			$varValue = array();
			$arrLanguages = $GLOBALS['TL_DCA']['tl_metamodel_rendersettings']['fields']['jumpTo']['eval']['columnFields']['langcode']['options'];
			foreach ($arrLanguages as $key => $lang)
			{
				$varValue[] = array('langcode' => $key, 'value' => '');
			}
		}
		return serialize($varValue);
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
		
		$objMetaModel = MetaModelFactory::byId(
				$this->Database->prepare('SELECT pid FROM tl_metamodel_rendersettings WHERE id = ?')
							   ->execute($objDC->id)
							   ->pid
				);
				
		if (!$objMetaModel)
		{
			throw new Exception('unexpected condition, metamodel unknown', 1);
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
	}
	
}

?>