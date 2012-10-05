<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Backend
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
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_rendersettings'] = array
(
	'config' => array
	(
		'dataContainer'               => 'General',
		'ptable'                      => 'tl_metamodel',
		'ctable'                      => 'tl_metamodel_rendersetting',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
		'oncreate_callback'	      	  => array(array('TableMetaModelRenderSettings', 'checkSortMode')),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'panelLayout'             => 'filter,limit',
			'headerFields'            => array('name'),
			'flag'                    => 1,
		),

		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			'label_callback'          => array('TableMetaModelRenderSettings', 'drawSetting')
		),

		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),

		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'settings' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['settings'],
				'href'                => 'table=tl_metamodel_rendersetting',
				'icon'                => 'system/modules/metamodels/html/rendersetting.png',
			),
		)
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('name', 'isdefault'),
			'settings' => array('template', 'jumpTo'),
			'expert' => array('mode', 'flag', 'fields', 'panelLayout')
		),
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'isdefault' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['isdefault'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 m12')
		),
		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['template'],
			'default'                 => 'catalog_full',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelRenderSettings','getTemplates'),
			'eval'                    => array()
		),
		'jumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true),
			'explanation'             => 'jumpTo'
		),
		'mode' => array
		(
		    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['mode'],
		    'exclude'                 => true,
		    'inputType'               => 'select',
		    'options'                 => array('1', '2'),
		    'eval'                    => array('tl_class'=>'w50', 'includeBlankOption' => true),
		    'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingmode']
		),
		'flag' => array
		(
		    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['flag'],
		    'exclude'                 => true,
		    'inputType'               => 'select',
		    'options'                 => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'),
		    'eval'                    => array('tl_class'=>'w50', 'includeBlankOption' => true),
		    'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']
		),
		'fields' => array
		(
		    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['fields'],
		    'exclude'                 => true,
		    'inputType'               => 'checkboxWizard',
		    'options_callback'        => array('TableMetaModelRenderSettings' ,'getAllAttributes'),
		    'eval'                    => array('tl_class'=>'w50', 'isAssociative' => true)
		),
		'panelLayout' => array
		(
		    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['panelLayout'],
		    'exclude'                 => true,
		    'inputType'               => 'text',
		    'eval'                    => array
		    (
			'tl_class'            => 'clr long wizard',
		    ),
		    'wizard'                  => array
		    (
			'stylepicker'         => array('TableMetaModelRenderSettings','getPanelpicker')
		    ),
		),
	),
);

?>