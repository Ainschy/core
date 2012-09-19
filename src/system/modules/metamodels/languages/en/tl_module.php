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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['metamodel']                = array('MetaModel', 'The MetaModel to list in this listing.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_use_limit']      = array('Use offset and limit for listing', 'Check if you want to limit the amount of items listed. This is useful for only showing the first 500 items or all excluding the first 10 items but keep pagination intact.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_offset']         = array('List offset', 'Please specify the offset value (i.e. 10 to skip the first 10 items).');
$GLOBALS['TL_LANG']['tl_module']['metamodel_limit']          = array('Maximum number of items', 'Please enter the maximum number of items. Enter 0 to show all items.');

$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby']         = array('Order by', 'Please choose the sort order.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filtering']      = array('Filter settings to apply', 'Select the filter settings that shall get applied when compiling the list.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_layout']         = array('Custom Template to use for generating', 'Select the template that shall be used for the selected attribute. Valid template files start with &quot;mod_metamodel_<type>&quot; where the module type name is put for &lt;type&gt;');
$GLOBALS['TL_LANG']['tl_module']['metamodel_rendersettings'] = array('Render settings to apply', 'Select the rendering settings to use for generating the output. If left empty, the default settings for the selected metamodel will get applied. If no default has been defined, the output will only get the raw values.');

/**
 * Wizards
 */

$GLOBALS['TL_LANG']['tl_module']['editmetamodel']            = array('Edit metamodel', 'Edit the metamodel ID %s.');
$GLOBALS['TL_LANG']['tl_module']['editrendersetting']        = array('Edit render setting', 'Edit the render setting ID %s.');
$GLOBALS['TL_LANG']['tl_module']['editfiltersetting']        = array('Edit filter setting', 'Edit the filter setting ID %s.');


?>