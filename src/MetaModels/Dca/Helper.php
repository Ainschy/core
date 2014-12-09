<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\IMetaModel;
use MetaModels\Factory as MetaModelFactory;

/**
 * This class is used as base class from dca handler classes for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Helper
{
    /**
     * Decode a language array.
     *
     * @param array|string $varValue     The value to decode.
     *
     * @param IMetaModel   $objMetaModel The MetaModel holding the languages.
     *
     * @return string
     */
    public static function decodeLangArray($varValue, IMetaModel $objMetaModel)
    {
        $arrLangValues = deserialize($varValue);
        if (!$objMetaModel->isTranslated()) {
            // If we have an array, return the first value and exit, if not an array, return the value itself.
            return is_array($arrLangValues) ? $arrLangValues[key($arrLangValues)] : $arrLangValues;
        }

        // Sort like in MetaModel definition.
        $arrLanguages = $objMetaModel->getAvailableLanguages();
        $arrOutput    = array();

        if ($arrLanguages) {
            foreach ($arrLanguages as $strLangCode) {
                if (is_array($arrLangValues)) {
                    $varSubValue = $arrLangValues[$strLangCode];
                } else {
                    $varSubValue = $arrLangValues;
                }

                if (is_array($varSubValue)) {
                    $arrOutput[] = array_merge($varSubValue, array('langcode' => $strLangCode));
                } else {
                    $arrOutput[] = array('langcode' => $strLangCode, 'value' => $varSubValue);
                }
            }
        }
        return serialize($arrOutput);
    }

    /**
     * Decode a language array.
     *
     * @param array|string $varValue     The value to decode.
     *
     * @param IMetaModel   $objMetaModel The MetaModel holding the languages.
     *
     * @return string
     */
    public static function encodeLangArray($varValue, IMetaModel $objMetaModel)
    {
        // Not translated, make it a plain string.
        if (!$objMetaModel->isTranslated()) {
            return $varValue;
        }
        $arrLangValues = deserialize($varValue);
        $arrOutput     = array();
        foreach ($arrLangValues as $varSubValue) {
            $strLangCode = $varSubValue['langcode'];
            unset($varSubValue['langcode']);
            if (count($varSubValue) > 1) {
                $arrOutput[$strLangCode] = $varSubValue;
            } else {
                $arrKeys                 = array_keys($varSubValue);
                $arrOutput[$strLangCode] = $varSubValue[$arrKeys[0]];
            }
        }
        return serialize($arrOutput);
    }

    /**
     * Create a widget for naming contexts. Use the language and translation information from the MetaModel.
     *
     * @param EnvironmentInterface $environment   The environment.
     *
     * @param PropertyInterface    $property      The property.
     *
     * @param IMetaModel           $metaModel     The MetaModel.
     *
     * @param string               $languageLabel The label to use for the language indicator.
     *
     * @param string               $valueLabel    The label to use for the input field.
     *
     * @param bool                 $isTextArea    If true, the widget will become a textarea, false otherwise.
     *
     * @param array                $arrValues     The values for the widget, needed to highlight the fallback language.
     *
     * @return void
     */
    public static function prepareLanguageAwareWidget(
        EnvironmentInterface $environment,
        PropertyInterface $property,
        IMetaModel $metaModel,
        $languageLabel,
        $valueLabel,
        $isTextArea,
        $arrValues
    ) {
        if (!$metaModel->isTranslated()) {
            $extra = $property->getExtra();

            $extra['tl_class'] .= 'w50';

            $property
                ->setWidgetType('text')
                ->setExtra($extra);

            return;
        }

        $fallback = $metaModel->getFallbackLanguage();

        $languages = array();
        foreach ((array) $metaModel->getAvailableLanguages() as $langCode) {
            $languages[$langCode] = $environment->getTranslator()->translate('LNG.' . $langCode, 'languages');
        }
        asort($languages);

        // Ensure we have the values present.
        if (empty($arrValues)) {
            foreach (array_keys($languages) as $langCode) {
                $arrValues[$langCode] = '';
            }
        }

        $rowClasses = array();
        foreach (array_keys($arrValues) as $langCode) {
            $rowClasses[] = ($langCode == $fallback) ? 'fallback_language' : 'normal_language';
        }

        $extra = $property->getExtra();

        $extra['minCount']       =
        $extra['maxCount']       = count($languages);
        $extra['disableSorting'] = true;
        $extra['tl_class']       = 'clr';
        $extra['columnFields']   = array
        (
            'langcode' => array
            (
                'label'                 => $languageLabel,
                'exclude'               => true,
                'inputType'             => 'justtextoption',
                'options'               => $languages,
                'eval'                  => array
                (
                    'rowClasses'        => $rowClasses,
                    'valign'            => 'center',
                    'style'             => 'min-width:75px;display:block;'
                )
            ),
            'value' => array
            (
                'label'                 => $valueLabel,
                'exclude'               => true,
                'inputType'             => $isTextArea ? 'textarea' : 'text',
                'eval'                  => array
                (
                    'rowClasses'        => $rowClasses,
                    'style'             => 'width:400px;',
                    'rows'              => 3
                )
            ),
        );

        $property
            ->setWidgetType('multiColumnWizard')
            ->setExtra($extra);
    }

    /**
     * Fetch the template group for the detail view of the current MetaModel module.
     *
     * @param string $templateBaseName The base name for the templates to retrieve.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated Use non static class MetaModels\BackendIntegration\TemplateList instead.
     */
    public static function getTemplatesForBase($templateBaseName)
    {
        $list = new TemplateList();
        $list->setServiceContainer($GLOBALS['container']['metamodels-service-container']);

        return $list->getTemplatesForBase($templateBaseName);
    }

    /**
     * Search all files with the given file extension below the given path.
     *
     * @param string $folder    The folder to scan.
     *
     * @param string $extension The file extension.
     *
     * @return array
     */
    public static function searchFiles($folder, $extension)
    {
        $scanResult = array();
        $result     = array();
        // Check if we have a file or folder.
        if (is_dir(TL_ROOT . '/' . $folder)) {
            $scanResult = scan(TL_ROOT . '/' . $folder);
        }

        // Run each value.
        foreach ($scanResult as $value) {
            if (!is_file(TL_ROOT . '/' . $folder . '/' . $value)) {
                $result += self::searchFiles($folder . '/' . $value, $extension);
            } else {
                if (preg_match('/'.$extension.'$/i', $value)) {
                    $result[$folder][$folder . '/' . $value] = $value;
                }
            }
        }

        return $result;
    }
}
