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
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['mm_filter_legend']				= 'MetaModel Filter';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['metamodel']                = array('MetaModel', 'Das MetaModel angeben, nach dem aufgelistet werden soll.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_use_limit']      = array('Datens�tze �berspringen und begrenzen', 'Ausw�hlen, um die Anzahl der aufgelisteten Datens�tze zu begrenzen. Die Einstellung wird ben�tigt, um beispielsweise die 500 ersten Datens�tze oder alle mit Ausnahme der ersten 10 Datens�tze aufzulisten und dabei eine korrekte Paginierung zu erm�glichen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_offset']         = array('Datens�tze �berspringen', 'Bitte die Anzahl der Datens�tze angeben, die �bersprungen werden sollen (zum Beispiel 10, um die ersten 10 Datens�tze zu �berspringen).');
$GLOBALS['TL_LANG']['tl_module']['metamodel_limit']          = array('Maximale Anzahl an Datens�tzen', 'Bitte die maximale Zahl der anzuzeigenden Datens�tze angeben. Um alle Datens�tze anzuzeigen und die Paginierung auszuschalten den Wert '0' eingeben.');

$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby']         = array('Sortieren nach', 'Bitte die Reihenfolge f�r die Sortierung ausw�hlen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby_direction'] = array('Sortierreihenfolge', 'Austeigende oder absteigende Reihenfolge.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filtering']      = array('Anzuwendende Filtereinstellungen', 'Die Filtereinstellungen ausw�hlen, die beim Zusammenstellen der Datensatzliste angewandt werden sollen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_layout']         = array('Eigenes Template f�r Datensatzliste ausw�hlen', 'Das Template ausw�hlen, das f�r die Generierung der Datensatzliste mit den ausgew�hlten Attributen benutzt werden soll. G�ltige Templatenamen beginnen mit &quot;mod_metamodel_&lt;type&gt;&quot;, wobei 'type' f�r den jeweiligen &lt;Typ&gt; steht.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_rendersettings'] = array('Anzuwendende Rendereinstellungen', 'Die Rendereinstellungen ausw�hlen, die benutzt werden sollen, um die Ausgabe zu erstellen. Falls leer werden die Standardeinstellungen f�r das ausgew�hlte MetaModel benutzt. Ist kein Standard definiert, dann werden Rohwerte ausgegeben.');


/**
 * Wizards
 */

$GLOBALS['TL_LANG']['tl_module']['editmetamodel']            = array('MetaModel bearbeiten', 'Das Metamodel ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_module']['editrendersetting']        = array('Darstellungsoptionen bearbeiten', 'Die Darstellungsoptionen von ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_module']['editfiltersetting']        = array('Filtereinstellung bearbeiten', 'Die Filtereinstellung von ID %s bearbeiten.');


?>