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


if (!is_array($GLOBALS['MM_AUTOLOAD']))
{
	$GLOBALS['MM_AUTOLOAD'] = array();
}

$GLOBALS['MM_AUTOLOAD'][] = dirname(__DIR__);
$GLOBALS['MM_AUTOLOAD'][] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'deprecated';

// For the moment, we add our autoloader at the end for non composerized Contao 2.X compat.
if (version_compare(VERSION, '3.0', '<') && !class_exists('Composer\Autoload\ClassLoader'))
{
	function metamodels_autoload($className)
	{
		$className = ltrim($className, '\\');
		$fileName  = '';

		if ($lastNsPos = strripos($className, '\\'))
		{
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= $className . '.php';

		foreach ($GLOBALS['MM_AUTOLOAD'] as $baseDir)
		{
			if (file_exists($baseDir . DIRECTORY_SEPARATOR . $fileName))
			{
				require $baseDir . DIRECTORY_SEPARATOR . $fileName;
				return true;
			}
		}

		return null;
	}

	spl_autoload_register('metamodels_autoload', true, false);
}

// Preserve values by extensions but insert as first entry after 'system'.
$arrOld = (array)$GLOBALS['BE_MOD']['metamodels'];
unset($GLOBALS['BE_MOD']['metamodels']);
array_insert($GLOBALS['BE_MOD'], (array_search('accounts', array_keys($GLOBALS['BE_MOD'])) + 1), array
(
	'metamodels' => array_replace_recursive(array
	(
		'metamodels' => array
		(
			'tables' => array
			(
				'tl_metamodel',
				'tl_metamodel_attribute',
				'tl_metamodel_filter',
				'tl_metamodel_filtersetting',
				'tl_metamodel_rendersettings',
				'tl_metamodel_rendersetting',
				'tl_metamodel_dca',
				'tl_metamodel_dcasetting',
				'tl_metamodel_dca_combine'
			),
			'icon'                  => 'system/modules/metamodels/html/logo.png',
			'dca_addall'            => array('TableMetaModelDcaSetting', 'addAll'),
			'rendersetting_addall'  => array('TableMetaModelRenderSetting', 'addAll'),
			'callback'              => 'MetaModels\BackendIntegration\Module'
		),
		'support_metamodels' => array
		(
			'icon'                  => 'system/modules/metamodels/html/support.png',
			'callback'              => 'MetaModels\BackendIntegration\Support'
		)
	),
	// Append all previous data here.
	$arrOld
	)
));

/*
	In order to add attribute types into the system, add the following snippet to your extension config.php:

	$GLOBALS['METAMODELS']['attributes']['TYPENAME']['class'] = 'TYPECLASS';
	$GLOBALS['METAMODELS']['attributes']['TYPENAME']['image'] = 'IMAGEPATH';
	$GLOBALS['METAMODELS']['attributes']['TYPENAME']['factory'] = 'FACTORYCLASS'; // optional

	where:
		TYPENAME     is the internal type name of your attribute.
		TYPECLASS    is the name of the implementing class.
		IMAGEPATH    path to an icon (16x16) that represents the attribute type. Based from TL_ROOT.
		FACTORYCLASS this is optional, if defined, the herein declared classname will be used for instantiation
		             of attributes of this type instead of plain constructing.
*/

/*
	In order to add filter rule types into the system, add the following snippet to your extension config.php:

	$GLOBALS['METAMODELS']['filters']['TYPENAME']['class']          = 'TYPECLASS';
	$GLOBALS['METAMODELS']['filters']['TYPENAME']['image']          = 'IMAGEPATH';
	$GLOBALS['METAMODELS']['filters']['TYPENAME']['info_callback']  = array(CALLBACK_CLASS, CALLBACK_METHOD);
	$GLOBALS['METAMODELS']['filters']['TYPENAME']['nestingAllowed'] = NESTINGVALUE // optional, default: false

	where:
		TYPENAME     is the internal type name of your filter setting.
		TYPECLASS    is the name of the implementing class.
		IMAGEPATH    path to an icon (16x16) that represents the filter rule type. Based from TL_ROOT.
		NESTINGVALUE boolean true or false. If this is true, you indicate that this rule may contain child rules.
*/
$GLOBALS['METAMODELS']['filters']['idlist']['class']                = 'MetaModels\Filter\Setting\IdList';
$GLOBALS['METAMODELS']['filters']['simplelookup']['class']          = 'MetaModels\Filter\Setting\SimpleLookup';
$GLOBALS['METAMODELS']['filters']['simplelookup']['info_callback']  = array('MetaModels\Dca\Filter', 'drawSimpleLookup');
$GLOBALS['METAMODELS']['filters']['customsql']['class']             = 'MetaModels\Filter\Setting\CustomSql';
$GLOBALS['METAMODELS']['filters']['customsql']['image']             = 'system/modules/metamodels/html/filter_customsql.png';
$GLOBALS['METAMODELS']['filters']['conditionand']['class']          = 'MetaModels\Filter\Setting\ConditionAnd';
$GLOBALS['METAMODELS']['filters']['conditionand']['image']          = 'system/modules/metamodels/html/filter_and.png';
$GLOBALS['METAMODELS']['filters']['conditionand']['info_callback']  = array('MetaModels\Dca\Filter', 'drawAndCondition');
$GLOBALS['METAMODELS']['filters']['conditionand']['nestingAllowed'] = true;
$GLOBALS['METAMODELS']['filters']['conditionor']['class']           = 'MetaModels\Filter\Setting\ConditionOr';
$GLOBALS['METAMODELS']['filters']['conditionor']['image']           = 'system/modules/metamodels/html/filter_or.png';
$GLOBALS['METAMODELS']['filters']['conditionor']['info_callback']   = array('MetaModels\Dca\Filter', 'drawOrCondition');
$GLOBALS['METAMODELS']['filters']['conditionor']['nestingAllowed']  = true;

/*
	All system columns that always are defined in a MetaModel table and are not attributes.
	When you alter this, consider to also change @link{MetaModelTableManipulation::STATEMENT_CREATE_TABLE}.
	Extensions will have to alter the table on their own as the columns will not get transported then.
*/
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'id';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'pid';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'sorting';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'tstamp';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'vargroup';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'varbase';

// Define our version so dependant extensions can use it in version_compare().
define('METAMODELS_VERSION', '0.1');

// Define some error levels.
define('METAMODELS_INFO', 3);
define('METAMODELS_WARN', 2);
define('METAMODELS_ERROR', 1);

// Back-end module - include only in Backend.
if (TL_MODE == 'BE')
{
	MetaModels\BackendIntegration\Boot::buildBackendMenu();
}

// Front-end modules.
$GLOBALS['FE_MOD']['metamodels']['metamodel_list']              = 'MetaModels\FrontendIntegration\Module\ModelList';
$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendfilter']   = 'MetaModels\FrontendIntegration\Module\Filter';
$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendclearall'] = 'MetaModels\FrontendIntegration\Module\FilterClearAll';

// Content elements.
$GLOBALS['TL_CTE']['metamodels']['metamodel_content']           = 'MetaModels\FrontendIntegration\Content\ModelList';
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendfilter']   = 'MetaModels\FrontendIntegration\Content\Filter';
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendclearall'] = 'MetaModels\FrontendIntegration\Content\FilterClearAll';

// Frontend widgets.
$GLOBALS['TL_FFL']['multitext'] = 'WidgetMultiText';
$GLOBALS['TL_FFL']['tags']      = 'WidgetTags';
$GLOBALS['TL_FFL']['range']     = 'WidgetRange';

// HOOKS.
$GLOBALS['TL_HOOKS']['loadDataContainer'][]      = array('MetaModels\BackendIntegration\Boot', 'createDataContainer');
$GLOBALS['TL_HOOKS']['loadDataContainer'][]      = array('MetaModels\Dca\MetaModelDcaBuilder', 'createDataContainer');
$GLOBALS['TL_HOOKS']['loadDataContainer'][]      = array('MetaModels\Dca\Filter', 'createDataContainer');
$GLOBALS['TL_HOOKS']['loadDataContainer'][]      = array('MetaModels\Dca\RenderSetting', 'createDataContainer');
$GLOBALS['TL_HOOKS']['loadDataContainer'][]      = array('MetaModels\Dca\DcaSetting', 'createDataContainer');
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('MetaModels\FrontendIntegration\FrontendFilter', 'generateClearAll');

// Dependencies we need.
// Mapping: extension folder => ER name.
$GLOBALS['METAMODELS']['dependencies']['metapalettes']      = 'MetaPalettes';
$GLOBALS['METAMODELS']['dependencies']['multicolumnwizard'] = 'MultiColumnWizard';
$GLOBALS['METAMODELS']['dependencies']['generalDriver']     = 'DC_General';
$GLOBALS['METAMODELS']['dependencies']['justtextwidgets']   = 'JustTextWidgets';

array_insert($GLOBALS['BE_FFL'], 15, array
(
	'mm_subdca'    => 'MetaModelSubDCAWidget'
));

// Selectable styles in the palette tl_class definitions.
$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['w50'],
	'cssclass' => 'w50',
	'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['w50x'],
	'cssclass' => 'w50x',
	'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['clr'],
	'cssclass' => 'clr',
	'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['clx'],
	'cssclass' => 'clx',
	'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['long'],
	'cssclass' => 'long',
	'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['wizard'],
	'cssclass' => 'wizard',
	'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['m12'],
	'cssclass' => 'm12',
	'image' => ''
);

// Selectable panels in the palette panelLayout definitions.
$GLOBALS['PALETTE_PANEL_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['panelLayout']['search'],
	'cssclass' => 'search',
	'image' => ''
);

$GLOBALS['PALETTE_PANEL_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['panelLayout']['sort'],
	'cssclass' => 'sort',
	'image' => ''
);

$GLOBALS['PALETTE_PANEL_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['panelLayout']['filter'],
	'cssclass' => 'filter',
	'image' => ''
);

$GLOBALS['PALETTE_PANEL_PICKER'][] = array
(
	'label' => &$GLOBALS['TL_LANG']['MSC']['panelLayout']['limit'],
	'cssclass' => 'limit',
	'image' => ''
);

// Initialize the filter parameters to an empty array if not initialized yet.
if (!isset($GLOBALS['MM_FILTER_PARAMS']))
{
	$GLOBALS['MM_FILTER_PARAMS'] = array();
}
