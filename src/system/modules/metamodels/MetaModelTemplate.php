<?php

class MetaModelTemplate
{
	/**
	 * Template file
	 * @var string
	 */
	protected $strTemplate;

	/**
	 * Output buffer
	 * @var string
	 */
	protected $strBuffer;

	/**
	 * Template data
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Makes all protected methods from class Controller callable publically.
	 */
	public function __call($strMethod, $arrArgs)
	{
		return call_user_func_array(array('MetaModelController', $strMethod), $arrArgs);
	}

	/**
	 * Create a new template instance
	 * @param string
	 * @param string
	 * @throws Exception
	 */
	public function __construct($strTemplate='')
	{
		$this->strTemplate = $strTemplate;
	}

	/**
	 * Set an object property
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}

	/**
	 * Return an object property
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		return $this->arrData[$strKey];
	}

	/**
	 * Check whether a property is set
	 * @param string
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
	}

	/**
	 * Set the template data from an array
	 * @param array
	 */
	public function setData($arrData)
	{
		$this->arrData = $arrData;
	}

	/**
	 * Return the template data as array
	 * @return array
	 */
	public function getData()
	{
		return $this->arrData;
	}

	/**
	 * Set the template name
	 * @param string
	 */
	public function setName($strTemplate)
	{
		$this->strTemplate = $strTemplate;
	}

	/**
	 * Return the template name
	 * @return string
	 */
	public function getName()
	{
		return $this->strTemplate;
	}

	/**
	 * Print all template variables to the screen using print_r
	 */
	public function showTemplateVars()
	{
		echo "<pre>\n";
		print_r($this->arrData);
		echo "</pre>\n";
	}

	/**
	 * Print all template variables to the screen using var_dump
	 */
	public function dumpTemplateVars()
	{
		echo "<pre>\n";
		var_dump($this->arrData);
		echo "</pre>\n";
	}

	/**
	 * Find a particular template file and return its path
	 * @param string
	 * @param string
	 * @return string
	 * @throws Exception
	 */
	protected function getTemplate($strTemplate, $strFormat='html5', $blnFailIfNotFound = false)
	{
		$strTemplate = basename($strTemplate);
		$strKey = $strFilename = $strTemplate . '.' . $strFormat;

		// Check for a theme folder
		if (TL_MODE == 'FE')
		{
			global $objPage;
			$strTemplateGroup = str_replace(array('../', 'templates/'), '', $objPage->templateGroup);

			if ($strTemplateGroup != '')
			{
				$strKey = $strTemplateGroup . '/' . $strKey;
			}
		}

		$objCache = FileCache::getInstance('templates');

		// Try to load the template path from the cache
		if (!$GLOBALS['TL_CONFIG']['debugMode'] && isset($objCache->$strKey))
		{
			if (file_exists(TL_ROOT . '/' . $objCache->$strKey))
			{
				return TL_ROOT . '/' . $objCache->$strKey;
			}
			else
			{
				unset($objCache->$strKey);
			}
		}

		$strPath = TL_ROOT . '/templates';

		// Check the theme folder first
		if (TL_MODE == 'FE' && $strTemplateGroup != '')
		{
			$strFile = $strPath . '/' . $strTemplateGroup . '/' . $strFilename;

			if (file_exists($strFile))
			{
				$objCache->$strKey = 'templates/' . $strTemplateGroup . '/' . $strFilename;
				return $strFile;
			}
		}

		// Then check the global templates directory
		$strFile = $strPath . '/' . $strFilename;

		if (file_exists($strFile))
		{
			$objCache->$strKey = 'templates/' . $strFilename;
			return $strFile;
		}

		// At last browse all module folders in reverse order
		foreach (array_reverse(Config::getInstance()->getActiveModules()) as $strModule)
		{
			$strFile = TL_ROOT . '/system/modules/' . $strModule . '/templates/' . $strFilename;

			if (file_exists($strFile))
			{
				$objCache->$strKey = 'system/modules/' . $strModule . '/templates/' . $strFilename;
				return $strFile;
			}
		}

		if ($blnFailIfNotFound)
		{
			throw new Exception('Could not find template file "' . $strFilename . '"');
		}
	}

	protected function callParseTemplateHook()
	{
		if (isset($GLOBALS['METAMODEL_HOOKS']['parseTemplate']) && is_array($GLOBALS['METAMODEL_HOOKS']['parseTemplate']))
		{
			foreach ($GLOBALS['METAMODEL_HOOKS']['parseTemplate'] as $callback)
			{
				list($strClass, $strMethod) = $callback;
				$objCallback = (in_array('getInstance', get_class_methods($strClass)))
					? call_user_func(array($strClass, 'getInstance'))
					: new $strClass();

				$objCallback->$strMethod($this);
			}
		}
	}

	/**
	 * Parse the template file and return it as string
	 * @return string
	 */
	public function parse($strOutputFormat, $blnFailIfNotFound = false)
	{
		if ($this->strTemplate == '')
		{
			return '';
		}

		// HOOK: add custom parse filters
		$this->callParseTemplateHook();

		$strTplFile = $this->getTemplate($this->strTemplate, $strOutputFormat, $blnFailIfNotFound);
		if ($strTplFile)
		{
			ob_start();
			include($strTplFile);
			$strBuffer = ob_get_contents();
			ob_end_clean();
	
			return $strBuffer;
		}
	}

	public static function render($strTemplate, $strOutputFormat, $arrTplData, $blnFailIfNotFound = false)
	{
		$objTemplate = new self($strTemplate);
		$objTemplate->setData($arrTplData);
		return $objTemplate->parse($strOutputFormat, $blnFailIfNotFound);
	}
}
