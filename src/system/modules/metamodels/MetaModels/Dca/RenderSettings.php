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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use DcGeneral\DC_General;

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
	 * Return the link picker wizard.
	 *
	 * @param DC_General $dc The DC_General currently in use.
	 *
	 * @return string
	 */
	public function pagePicker(DC_General $dc)
	{
		$environment = $dc->getEnvironment();

		if (version_compare(VERSION, '3.0', '<'))
		{
			$event = new GenerateHtmlEvent(
				'pickpage.gif',
				$environment->getTranslator()->translate('MSC.pagepicker'),
				'style="vertical-align:top;cursor:pointer" onclick="Backend.pickPage(\'ctrl_' . $dc->inputName . '\')"'
			);
		}
		else
		{
			$url = sprintf('%scontao/page.php?do=metamodels&table=tl_metamodel_rendersettings&field=ctrl_%s',
				\Environment::get('base'),
				$dc->inputName
			);

			$options = sprintf(
				"{'width':765,'title':'%s','url':'%s','id':'%s','tag':'ctrl_%s','self':this}",
				$environment->getTranslator()->translate('MOD.page.0'),
				$url,
				$dc->inputName,
				$dc->inputName
			);

			$event = new GenerateHtmlEvent(
				'pickpage.gif',
				$environment->getTranslator()->translate('MSC.pagepicker'),
				'style="vertical-align:top;cursor:pointer" onclick="Backend.openModalSelector(' . $options . ')"'
			);
		}

		$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $event);

		return ' ' . $event->getHtml();
	}
}

