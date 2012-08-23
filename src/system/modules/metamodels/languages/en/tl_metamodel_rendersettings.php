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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['name']                 = array('Name', 'Setting name.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['tstamp']               = array('Revision date', 'Date and time of the latest revision');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['title_legend']         = 'Name';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['new']                  = array('New', 'Create new setting.');

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['edit']                 = array('Edit setting', 'Edit setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['copy']                 = array('Copy setting definiton', 'Copy setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['delete']               = array('Delete setting', 'Delete setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['show']                 = array('Filter details', 'Show details of setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['settings']               = array('Define attribute settings', 'Define attribute settings for setting ID %s');

?>