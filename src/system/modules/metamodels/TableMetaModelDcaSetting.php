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

class TableMetaModelDcaSetting extends Backend
{
	/**
	 * @var TableMetaModelRenderSetting
	 */
	protected static $objInstance = null;

	protected $objMetaModel = null;

	protected $objSetting = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return MetaPalettes
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new TableMetaModelDcaSetting();
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

	public function createDataContainer($strTableName)
	{
		if ($strTableName != 'tl_metamodel_dcasetting')
		{
			return;
		}
		$this->objectsFromUrl(null);

		if (!$this->objMetaModel)
		{
			return;
		}

		$objMetaModel=$this->objMetaModel;

		return;
		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strColName = sprintf('%s_%s', $objAttribute->getColName(), $objAttribute->get('id'));
			$strTypeName = $objAttribute->get('type');
			// GOTCHA: watch out to never ever use anyting numeric in the palettes anywhere else.
			// We have the problem, that DC_Table does not call the load callback when determining the palette.
			$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes'][$objAttribute->get('id') . ' extends ' . $strTypeName] = array();
		}
	}

    protected function setLegend (IMetaModel $objMetaModel)
    {
        $this->loadLanguageFile('languages');
        $arrLegend = array();
        if(!$objMetaModel->isTranslated())
        {
            $arrLegend = array
            (
                'inputType'            => 'text',
                'eval'                 => array
                (
                    'tl_class'         => 'w50',
                )

            );
        } else {
            $arrLanguages = array();
            foreach((array)$objMetaModel->getAvailableLanguages() as $strLangCode)
            {
                $arrLanguages[$strLangCode] = $GLOBALS['TL_LANG']['LNG'][$strLangCode];
            }
            asort($arrLanguages);

            $arrLegend = array
            (
                'inputType'        => 'multiColumnWizard',
                'eval'             => array
                (
                    'minCount' => count($arrLanguages),
                    'maxCount' => count($arrLanguages),
                    'disableSorting' => true,
                    'tl_class' => 'clr',
                    'columnFields' => array
                    (
                        'langcode' => array
                        (
                            'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_langcode'],
                            'exclude'               => true,
                            'inputType'             => 'justtextoption',
                            'options'               => $arrLanguages,
                            'eval'                  => array
                            (
                                'valign'            => 'center'
                            )
                        ),
                        'value' => array
                        (
                            'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_value'],
                            'exclude'               => true,
                            'inputType'             => 'text',
                            'eval'                     => array
                            (
                                'style'             => 'width:400px;'
                            )
                        ),
                    )
                ),
                'load_callback' => array
                (
                    array('TableMetaModelDcaSetting', 'decodeLangArray')
                ),
                'save_callback' => array
                (
                    array('TableMetaModelDcaSetting', 'encodeLangArray')
                )
            );
        }

        $GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['fields']['legendtitle'] = array_replace_recursive($arrLegend, $GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['fields']['legendtitle']);
    }

    /**
     * Retrieve and buffer the current value of the column frm the DB.
     * This will later be used for the on submit and onsave callbacks.
     *
     * Used from tl_metamodel_attribute DCA
     *
     * @param DataContainer $objDC the data container that issued this callback.
     */
    public function onLoadCallback($objDC)
    {
        // do nothing if not in edit mode.
        if(!($objDC->id && $this->Input->get('act')) || ($this->Input->get('act') == 'paste'))
        {
            return;
        }

        $this->objectsFromUrl($objDC);
        $this->setLegend($this->objMetaModel);
    }

	protected function objectsFromUrl($objDC)
	{
		// TODO: detect all other ways we might end up here and fetch $objMetaModel accordingly.
		if ($this->objMetaModel)
		{
			return;
		}

		if($objDC && $objDC->getCurrentModel())
		{
			$this->objSetting = $this->Database->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')->execute($objDC->getCurrentModel()->getProperty('pid'));
			$this->objMetaModel = MetaModelFactory::byId($this->objSetting->pid);
		}

        if ($this->Input->get('act'))
        {
            // act present, but we have an id
            switch ($this->Input->get('act'))
            {
                case 'edit':
                    if ($this->Input->get('id'))
                    {
                        $this->objSetting = $this->Database->prepare('
                            SELECT tl_metamodel_dcasetting.*,
                                tl_metamodel_dca.pid AS tl_metamodel_dca_pid
                            FROM tl_metamodel_dcasetting
                            LEFT JOIN tl_metamodel_dca
                            ON (tl_metamodel_dcasetting.pid = tl_metamodel_dca.id)
                            WHERE (tl_metamodel_dcasetting.id=?)')
                            ->execute($this->Input->get('id'));
                        $this->objMetaModel = MetaModelFactory::byId($this->objSetting->tl_metamodel_dca_pid);
                    }
                    break;
                default:;
            }
        } else {
        }
	}

	/**
	 * Prepares a option list with alias => name connection for all attributes.
	 * This is used in the attr_id select box.
	 *
	 * @param DataContainer $objDC the data container calling.
	 *
	 * @return
	 */
	public function getAttributeNames($objDC)
	{
		$this->objectsFromUrl($objDC);
		$arrResult = array();
		if (!$this->objMetaModel)
		{
			return;
		}
		$objMetaModel = $this->objMetaModel;

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strTypeName = $objAttribute->get('type');
			$arrResult[$objAttribute->get('id')] = $objAttribute->getName() . ' [' . $strTypeName . ']';
		}

		return $arrResult;
	}

	public function drawSetting($arrRow, $strLabel = '', DataContainer $objDC = null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		switch ($arrRow['dcatype'])
		{
			case 'attribute':
				$objSetting = $this->Database->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')->execute($arrRow['pid']);
				$objMetaModel = MetaModelFactory::byId($objSetting->pid);

				if (!$objMetaModel)
				{
					return '';
				}

				$objAttribute = $objMetaModel->getAttributeById($arrRow['attr_id']);

				if (!$objAttribute)
				{
					return '';
				}

				$strImage = $GLOBALS['METAMODELS']['attributes'][$objAttribute->get('type')]['image'];

				if (!$strImage || !file_exists(TL_ROOT . '/' . $strImage))
				{
					$strImage = 'system/modules/metamodels/html/filter_default.png';
				}

				$strImage = $this->generateImage($strImage, '', $imageAttribute);

				// Return the image only
				if ($blnReturnImage)
				{
					return $strImage;
				}

				$strLabel = $objAttribute->getName();

				$strReturn = sprintf(
					$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['row'],
					$strImage,
					$strLabel ? $strLabel : $objAttribute->get('type'),
					$objAttribute->get('type')
				);
				return $strReturn;
			break;

			case 'legend':
                $arrLegend = deserialize($arrRow['legendtitle']);
                if(is_array($arrLegend))
                {
                    $strLegend = $arrLegend[$GLOBALS['TL_LANGUAGE']];
                    
                    if(!$strLegend)
                    {
                        // TODO: Get the fallback language here
                        $strLegend = 'legend';
                    }
                } else {
                    $strLegend = $arrRow['legendtitle'] ? $arrRow['legendtitle'] : 'legend';
                }

                $strReturn = sprintf(
                    $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legend_row'],
                    $strLegend, $arrRow['legendhide'] ? ':hide' : ''
                );
				
				return $strReturn;
			break;

			default:
			break;
		}
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
		if (!($this->objMetaModel))
		{
			return array();
		}

		$objAttribute = $this->objMetaModel->getAttributeById($objDC->activeRecord->attr_id);

		if (!$objAttribute)
		{
			return array();
		}

		return $this->getTemplateGroup('mm_attr_' . $objAttribute->get('type') /*, theme id how the heck shall I fetch that one? */);
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	/**
	 * Generate module
	 */
	public function addAll()
	{
		$this->loadLanguageFile('default');
		$this->loadLanguageFile('tl_metamodel_dcasetting');

		$this->Template = new BackendTemplate('be_autocreatepalette');

		$this->Template->cacheMessage = '';
		$this->Template->updateMessage = '';

		$this->Template->href = $this->getReferer(true);
		$this->Template->headline = $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'][1];

		// severity: error, confirm, info, new
		$arrMessages = array();

		$objPalette = $this->Database->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')->execute($this->Input->get('id'));

		$objMetaModel = MetaModelFactory::byId($objPalette->pid);

		$objAlreadyExist = $this->Database->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? AND dcatype=?')->execute($this->Input->get('id'), 'attribute');

		$arrKnown = array();
		$intMax = 128;
		while ($objAlreadyExist->next())
		{
			$arrKnown[$objAlreadyExist->attr_id] = $objAlreadyExist->row();
			if ($intMax< $objAlreadyExist->sorting)
			{
				$intMax = $objAlreadyExist->sorting;
			}
		}

		$blnWantPerform = false;
		// perform the labour work
		if ($this->Input->post('act') == 'perform')
		{
			// loop over all attributes now.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (!array_key_exists($objAttribute->get('id'), $arrKnown))
				{
					$intMax *= 128;
					$this->Database->prepare('INSERT INTO tl_metamodel_dcasetting %s')->set(array(
						'pid'      => $this->Input->get('id'),
						'sorting'  => $intMax,
						'tstamp'   => time(),
						'dcatype'  => 'attribute',
						'attr_id'  => $objAttribute->get('id'),
						'tl_class' => ''
					))->execute();
					$arrMessages[sprintf('added attribute %s to palette.', $objAttribute->getName())] = 'confirm';
				}
			}
		} else {
			// loop over all attributes now.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (array_key_exists($objAttribute->get('id'), $arrKnown))
				{
					$arrMessages[sprintf('Attribute %s already in palette.', $objAttribute->getName())] = 'info';
				} else {
					$arrMessages[sprintf('will add attribute %s to palette.', $objAttribute->getName())] = 'confirm';
					$blnWantPerform = true;
				}
			}
		}

		if ($blnWantPerform)
		{
			$this->Template->action = ampersand($this->Environment->request);
			$this->Template->submit = $GLOBALS['TL_LANG']['MSC']['continue'];
		} else {
			$this->Template->action = ampersand($this->getReferer(true));
			$this->Template->submit = $GLOBALS['TL_LANG']['MSC']['saveNclose'];
		}

		$this->Template->error = $arrMessages;

		return $this->Template->parse();
	}

    public function decodeLangArray($varValue, $objDC)
    {
        $arrLangValues = (array)deserialize($varValue);

        // check for predefined values.
        $objDB = Database::getInstance();
        // fetch current values of the field from DB.
        $objField = $objDB->prepare('
            SELECT *
            FROM tl_metamodel_dcasetting
            WHERE id=?'
        )
        ->limit(1)
        ->executeUncached($objDC->id);

        if ($objField->numRows == 1)
        {
            $objMetaModel = MetaModelFactory::byId($objField->pid);
            if ($objMetaModel)
            {
                // sort like in metamodel definition
                $arrLanguages = $objMetaModel->getAvailableLanguages();
                if ($arrLanguages)
                {
                    $arrOutput = array();
                    foreach($arrLanguages as $strLangCode)
                    {
                        $varSubValue = $arrLangValues[$strLangCode];
                        if (is_array($varSubValue))
                        {
                            $arrOutput[] = array_merge($varSubValue, array('langcode' => $strLangCode));
                        } else {
                            $arrOutput[] = array('langcode' => $strLangCode, 'value' => $varSubValue);
                        }
                    }
                }
                return serialize($arrOutput);
            }
        }

        // fallthrough, plain reordering.
        $arrOutput = array();
        foreach ($arrLangValues as $strLangCode => $varSubValue)
        {
            if (is_array($varSubValue))
            {
                $arrOutput[] = array_merge($varSubValue, array('langcode' => $strLangCode));
            } else {
                $arrOutput[] = array('langcode' => $strLangCode, 'value' => $varSubValue);
            }
        }
        return serialize($arrOutput);
    }

    public function encodeLangArray($varValue)
    {
        $arrLangValues = deserialize($varValue);
        $arrOutput = array();
        foreach ($arrLangValues as $varSubValue)
        {
            $strLangCode = $varSubValue['langcode'];
            unset($varSubValue['langcode']);
            if (count($varSubValue) > 1)
            {
                $arrOutput[$strLangCode] = $varSubValue;
            } else {
                $arrKeys = array_keys($varSubValue);
                $arrOutput[$strLangCode] = $varSubValue[$arrKeys[0]];
            }
        }
        return serialize($arrOutput);
    }

	public function getStylepicker($objDC)
	{
		return sprintf(
			' <a href="system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s" rel="lightbox[files 765 60%%]" data-lightbox="files 765 60%%">%s</a>',
			$objDC->table,
			$objDC->field,
			$objDC->inputName,
			$objDC->id,
			$this->generateImage('system/modules/metamodels/html/dca_wizard.png', $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['stylepicker'], 'style="vertical-align:top;"')
		);
	}
}

?>