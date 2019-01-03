<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultFilterElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultLimitElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSearchElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSortElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSubmitElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SearchElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SubmitElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelRowCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelRowInterface;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\Helper\ViewCombinations;

/**
 * This class handles the panel building.
 */
class PanelBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombinations
     */
    private $viewCombinations;

    /**
     * The input screen to use (only set during build phase).
     *
     * @var IInputScreen
     */
    private $inputScreen;

    /**
     * Create a new instance.
     *
     * @param ViewCombinations $viewCombinations The view combinations.
     */
    public function __construct(ViewCombinations $viewCombinations)
    {
        $this->viewCombinations = $viewCombinations;
    }

    /**
     * Build the data definition.
     *
     * @param IMetaModelDataDefinition $container The container to populate.
     *
     * @return void
     */
    protected function build(IMetaModelDataDefinition $container)
    {
        $this->inputScreen = $this->viewCombinations->getInputScreenDetails($container->getName());

        // Check if we have a BackendViewDef.
        if ($container->hasDefinition(Contao2BackendViewDefinitionInterface::NAME)) {
            /** @var Contao2BackendViewDefinitionInterface $view */
            $view = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        } else {
            return;
        }

        // Get the panel layout.
        $panelLayout = $this->inputScreen->getPanelLayout();

        // Check if we have a layout.
        if (empty($panelLayout)) {
            return;
        }

        // Get the layout from the dca.
        $arrRows = trimsplit(';', $panelLayout);

        // Create a new panel container.
        $panel     = $view->getPanelLayout();
        $panelRows = $panel->getRows();

        foreach ($arrRows as $rowNo => $rowElements) {
            // Get the row, if we have one or create a new one.
            if ($panelRows->getRowCount() < ($rowNo + 1)) {
                $panelRow = $panelRows->addRow();
            } else {
                $panelRow = $panelRows->getRow($rowNo);
            }

            // Get the fields.
            $fields = trimsplit(',', $rowElements);
            $fields = array_reverse($fields);

            $this->parsePanelRow($fields, $panelRow);

            // If we have no entries for this row, remove it.
            if ($panelRow->getCount() == 0) {
                $panelRows->deleteRow($rowNo);
            }
        }

        $this->ensureSubmitElement($panelRows);
        $this->inputScreen = null;
    }

    /**
     * Ensure at least one submit element is present in any of the rows.
     *
     * If no submit element is present, this method will create one at the end of the last row.
     *
     * @param PanelRowCollectionInterface $panelRows The panel rows.
     *
     * @return void
     */
    private function ensureSubmitElement($panelRows)
    {
        // Check if we have a submit button.
        $hasSubmit = false;
        foreach ($panelRows as $panelRow) {
            foreach ($panelRow as $element) {
                if ($element instanceof SubmitElementInformationInterface) {
                    $hasSubmit = true;
                    break;
                }

                if ($hasSubmit) {
                    break;
                }
            }
        }

        // If not add a submit.
        if (!$hasSubmit && $panelRows->getRowCount()) {
            $row = $panelRows->getRow($panelRows->getRowCount() - 1);
            $row->addElement(new DefaultSubmitElementInformation(), 0);
        }
    }

    /**
     * Parse a single row with all elements.
     *
     * @param array             $fields   A list of fields for adding to the row.
     *
     * @param PanelRowInterface $panelRow The row container itself.
     *
     * @return void
     */
    private function parsePanelRow($fields, PanelRowInterface $panelRow)
    {
        // Parse each type.
        foreach ($fields as $field) {
            switch ($field) {
                case 'sort':
                    $this->parsePanelSort($panelRow);
                    break;

                case 'limit':
                    $this->parsePanelLimit($panelRow);
                    break;

                case 'filter':
                    $this->parsePanelFilter($panelRow);
                    break;

                case 'search':
                    $this->parsePanelSearch($panelRow);
                    break;

                case 'submit':
                    $this->parsePanelSubmit($panelRow);
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Add filter elements to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    private function parsePanelFilter(PanelRowInterface $row)
    {
        foreach ($this->inputScreen->getProperties() as $property => $value) {
            if (!empty($value['info']['filter'])) {
                $element = new DefaultFilterElementInformation();
                $element->setPropertyName($property);
                if (!$row->hasElement($element->getName())) {
                    $row->addElement($element);
                }
            }
        }
    }

    /**
     * Add sort element to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    private function parsePanelSort(PanelRowInterface $row)
    {
        if (!$row->hasElement('sort')) {
            $element = new DefaultSortElementInformation();
            $row->addElement($element);
        }
    }

    /**
     * Add search element to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the search element does not implement the correct interface.
     */
    private function parsePanelSearch(PanelRowInterface $row)
    {
        if ($row->hasElement('search')) {
            $element = $row->getElement('search');
        } else {
            $element = new DefaultSearchElementInformation();
        }

        if (!$element instanceof SearchElementInformationInterface) {
            throw new \InvalidArgumentException('Search element does not implement the correct interface.');
        }

        foreach ($this->inputScreen->getProperties() as $property => $value) {
            if (!empty($value['info']['search'])) {
                $element->addProperty($property);
            }
        }

        if ($element->getPropertyNames() && !$row->hasElement('search')) {
            $row->addElement($element);
        }
    }

    /**
     * Add  elements to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    private function parsePanelLimit(PanelRowInterface $row)
    {
        if (!$row->hasElement('limit')) {
            $row->addElement(new DefaultLimitElementInformation());
        }
    }

    /**
     * Add  elements to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    private function parsePanelSubmit(PanelRowInterface $row)
    {
        if (!$row->hasElement('submit')) {
            $row->addElement(new DefaultSubmitElementInformation());
        }
    }
}
