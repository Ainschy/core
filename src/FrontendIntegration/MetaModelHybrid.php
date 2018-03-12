<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\FormModel;
use Contao\Hybrid;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsServiceContainer;

/**
 * Base implementation of a MetaModel Hybrid element.
 *
 * @property string metamodel                The id of the MetaModel to use.
 * @property string metamodel_filtering      The id of the MetaModel filter setting to use.
 * @property string metamodel_rendersettings The id of the MetaModel render setting to use.
 */
abstract class MetaModelHybrid extends Hybrid
{
    /**
     * The name to display in the wildcard.
     *
     * @var string
     */
    protected $wildCardName;

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $wildCardLink;

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $typePrefix;

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated The service container will get removed, inject needed services instead.
     */
    public function getServiceContainer()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return System::getContainer()->get(MetaModelsServiceContainer::class);
    }

    /**
     * Retrieve the factory.
     *
     * @return IFactory
     */
    protected function getFactory()
    {
        return System::getContainer()->get('metamodels.factory');
    }

    /**
     * Retrieve the connection.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        return System::getContainer()->get('database_connection');
    }

    /**
     * Create a new instance.
     *
     * @param ContentModel|ModuleModel|FormModel $objElement The object from the database.
     *
     * @param string                             $strColumn  The column the element is displayed within.
     */
    public function __construct($objElement, $strColumn = 'main')
    {
        parent::__construct($objElement, $strColumn);

        $this->arrData = $objElement->row();
        // Get space and CSS ID from the parent element (!)
        $this->space      = StringUtil::deserialize($objElement->space);
        $this->cssID      = StringUtil::deserialize($objElement->cssID, true);
        $this->typePrefix = $objElement->typePrefix;
        $this->strKey     = $objElement->type;
        $arrHeadline      = StringUtil::deserialize($objElement->headline);
        $this->headline   = is_array($arrHeadline) ? $arrHeadline['value'] : $arrHeadline;
        $this->hl         = is_array($arrHeadline) ? $arrHeadline['unit'] : 'h1';
    }

    /**
     * Generate the list.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $strInfo = '';
            if ($this->metamodel) {
                // Add CSS file.
                $GLOBALS['TL_CSS'][] = 'system/modules/metamodels/assets/css/style.css';

                // Retrieve name of MetaModels.
                $infoTemplate =
                    '<div class="wc_info tl_gray"><span class="wc_label"><abbr title="%s">%s:</abbr></span> %s</div>';

                $factory       = $this->getFactory();
                $metaModelName = $factory->translateIdToMetaModelName($this->metamodel);
                $metaModel     = $factory->getMetaModel($metaModelName);
                $strInfo       = sprintf(
                    $infoTemplate,
                    $GLOBALS['TL_LANG']['MSC']['mm_be_info_name'][1],
                    $GLOBALS['TL_LANG']['MSC']['mm_be_info_name'][0],
                    $metaModel->getName()
                );

                $database = $this->getConnection();

                // Retrieve name of filter.
                if ($this->metamodel_filtering) {
                    $infoFi = $database
                        ->createQueryBuilder()
                        ->select('name')
                        ->from('tl_metamodel_filter')
                        ->where('id=:id')
                        ->setParameter('id', $this->metamodel_filtering)
                        ->execute()
                        ->fetch(\PDO::FETCH_COLUMN);

                    if ($infoFi) {
                        $strInfo .= sprintf(
                            $infoTemplate,
                            $GLOBALS['TL_LANG']['MSC']['mm_be_info_filter'][1],
                            $GLOBALS['TL_LANG']['MSC']['mm_be_info_filter'][0],
                            $infoFi
                        );
                    }
                }

                // Retrieve name of rendersetting.
                if ($this->metamodel_rendersettings) {
                    $infoRs = $database
                        ->createQueryBuilder()
                        ->select('name')
                        ->from('tl_metamodel_rendersettings')
                        ->where('id=:id')
                        ->setParameter('id', $this->metamodel_rendersettings)
                        ->execute()
                        ->fetch(\PDO::FETCH_COLUMN);

                    if ($infoRs) {
                        $strInfo .= sprintf(
                            $infoTemplate,
                            $GLOBALS['TL_LANG']['MSC']['mm_be_info_render_setting'][1],
                            $GLOBALS['TL_LANG']['MSC']['mm_be_info_render_setting'][0],
                            $infoRs
                        );
                    }
                }
            }

            $objTemplate           = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = $this->wildCardName . $strInfo;
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = ($this->typePrefix == 'mod_' ? 'FE-Modul: ' : '') . $this->name;
            $objTemplate->href     = sprintf($this->wildCardLink, $this->id);

            return $objTemplate->parse();
        }

        return parent::generate();
    }
}
