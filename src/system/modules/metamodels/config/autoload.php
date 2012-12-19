<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * @package Metamodels
 * @link    http://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'IMetaModelAttribute'                   => 'system/modules/metamodels/IMetaModelAttribute.php',
	'ContentMetaModel'                      => 'system/modules/metamodels/ContentMetaModel.php',
	'GeneralCallbackMetaModel'              => 'system/modules/metamodels/GeneralCallbackMetaModel.php',
	'GeneralControllerMetaModel'            => 'system/modules/metamodels/GeneralControllerMetaModel.php',
	'GeneralDataMetaModel'                  => 'system/modules/metamodels/GeneralDataMetaModel.php',
	'GeneralModelMetaModel'                 => 'system/modules/metamodels/GeneralModelMetaModel.php',
	'GeneralModelMetaModelIterator'         => 'system/modules/metamodels/GeneralModelMetaModelIterator.php',
	'IMetaModel'                            => 'system/modules/metamodels/IMetaModel.php',
	'IMetaModelAttributeComplex'            => 'system/modules/metamodels/IMetaModelAttributeComplex.php',
	'IMetaModelAttributeFactory'            => 'system/modules/metamodels/IMetaModelAttributeFactory.php',
	'IMetaModelAttributeSimple'             => 'system/modules/metamodels/IMetaModelAttributeSimple.php',
	'IMetaModelAttributeTranslated'         => 'system/modules/metamodels/IMetaModelAttributeTranslated.php',
	'IMetaModelFactory'                     => 'system/modules/metamodels/IMetaModelFactory.php',
	'IMetaModelFilter'                      => 'system/modules/metamodels/IMetaModelFilter.php',
	'IMetaModelFilterRule'                  => 'system/modules/metamodels/IMetaModelFilterRule.php',
	'IMetaModelFilterSetting'               => 'system/modules/metamodels/IMetaModelFilterSetting.php',
	'IMetaModelFilterSettingWithChilds'     => 'system/modules/metamodels/IMetaModelFilterSettingWithChilds.php',
	'IMetaModelFilterSettings'              => 'system/modules/metamodels/IMetaModelFilterSettings.php',
	'IMetaModelFilterSettingsFactory'       => 'system/modules/metamodels/IMetaModelFilterSettingsFactory.php',
	'IMetaModelItem'                        => 'system/modules/metamodels/IMetaModelItem.php',
	'IMetaModelItems'                       => 'system/modules/metamodels/IMetaModelItems.php',
	'IMetaModelRenderSettings'              => 'system/modules/metamodels/IMetaModelRenderSettings.php',
	'IMetaModelRenderSettingsFactory'       => 'system/modules/metamodels/IMetaModelRenderSettingsFactory.php',
	'MetaModel'                             => 'system/modules/metamodels/MetaModel.php',
	'MetaModelAttribute'                    => 'system/modules/metamodels/MetaModelAttribute.php',
	'MetaModelAttributeComplex'             => 'system/modules/metamodels/MetaModelAttributeComplex.php',
	'MetaModelAttributeFactory'             => 'system/modules/metamodels/MetaModelAttributeFactory.php',
	'MetaModelAttributeHybrid'              => 'system/modules/metamodels/MetaModelAttributeHybrid.php',
	'MetaModelAttributeSimple'              => 'system/modules/metamodels/MetaModelAttributeSimple.php',
	'MetaModelAttributeTranslatedReference' => 'system/modules/metamodels/MetaModelAttributeTranslatedReference.php',
	'MetaModelBackendModule'                => 'system/modules/metamodels/MetaModelBackendModule.php',
	'MetaModelController'                   => 'system/modules/metamodels/MetaModelController.php',
	'MetaModelDatabase'                     => 'system/modules/metamodels/MetaModelDatabase.php',
	'MetaModelDcaBuilder'                   => 'system/modules/metamodels/MetaModelDcaBuilder.php',
	'MetaModelDcaCombiner'                  => 'system/modules/metamodels/MetaModelDcaCombiner.php',
	'MetaModelFactory'                      => 'system/modules/metamodels/MetaModelFactory.php',
	'MetaModelFilter'                       => 'system/modules/metamodels/MetaModelFilter.php',
	'MetaModelFilterRuleAND'                => 'system/modules/metamodels/MetaModelFilterRuleAND.php',
	'MetaModelFilterRuleOR'                 => 'system/modules/metamodels/MetaModelFilterRuleOR.php',
	'MetaModelFilterRuleSearchAttribute'    => 'system/modules/metamodels/MetaModelFilterRuleSearchAttribute.php',
	'MetaModelFilterRuleSimpleQuery'        => 'system/modules/metamodels/MetaModelFilterRuleSimpleQuery.php',
	'MetaModelFilterRuleStaticIdList'       => 'system/modules/metamodels/MetaModelFilterRuleStaticIdList.php',
	'MetaModelFilterSetting'                => 'system/modules/metamodels/MetaModelFilterSetting.php',
	'MetaModelFilterSettingConditionAnd'    => 'system/modules/metamodels/MetaModelFilterSettingConditionAnd.php',
	'MetaModelFilterSettingConditionOr'     => 'system/modules/metamodels/MetaModelFilterSettingConditionOr.php',
	'MetaModelFilterSettingCustomSQL'       => 'system/modules/metamodels/MetaModelFilterSettingCustomSQL.php',
	'MetaModelFilterSettingIdList'          => 'system/modules/metamodels/MetaModelFilterSettingIdList.php',
	'MetaModelFilterSettingSimpleLookup'    => 'system/modules/metamodels/MetaModelFilterSettingSimpleLookup.php',
	'MetaModelFilterSettingWithChilds'      => 'system/modules/metamodels/MetaModelFilterSettingWithChilds.php',
	'MetaModelFilterSettings'               => 'system/modules/metamodels/MetaModelFilterSettings.php',
	'MetaModelFilterSettingsFactory'        => 'system/modules/metamodels/MetaModelFilterSettingsFactory.php',
	'MetaModelItem'                         => 'system/modules/metamodels/MetaModelItem.php',
	'MetaModelItems'                        => 'system/modules/metamodels/MetaModelItems.php',
	'MetaModelList'                         => 'system/modules/metamodels/MetaModelList.php',
	'MetaModelRenderSettings'               => 'system/modules/metamodels/MetaModelRenderSettings.php',
	'MetaModelRenderSettingsFactory'        => 'system/modules/metamodels/MetaModelRenderSettingsFactory.php',
	'MetaModelSubDCAWidget'                 => 'system/modules/metamodels/MetaModelSubDCAWidget.php',
	'MetaModelTableManipulation'            => 'system/modules/metamodels/MetaModelTableManipulation.php',
	'MetaModelTemplate'                     => 'system/modules/metamodels/MetaModelTemplate.php',
	'ModuleMetaModelList'                   => 'system/modules/metamodels/ModuleMetaModelList.php',
	'TableMetaModel'                        => 'system/modules/metamodels/TableMetaModel.php',
	'TableMetaModelAttribute'               => 'system/modules/metamodels/TableMetaModelAttribute.php',
	'TableMetaModelDca'                     => 'system/modules/metamodels/TableMetaModelDca.php',
	'TableMetaModelDcaSetting'              => 'system/modules/metamodels/TableMetaModelDcaSetting.php',
	'TableMetaModelFilterSetting'           => 'system/modules/metamodels/TableMetaModelFilterSetting.php',
	'TableMetaModelHelper'                  => 'system/modules/metamodels/TableMetaModelHelper.php',
	'TableMetaModelRenderSetting'           => 'system/modules/metamodels/TableMetaModelRenderSetting.php',
	'TableMetaModelRenderSettings'          => 'system/modules/metamodels/TableMetaModelRenderSettings.php',
	'MetaModelBackend'                      => 'system/modules/metamodels/MetaModelBackend.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_autocreatepalette'  => 'system/modules/metamodels/templates',
	'be_autocreateview'     => 'system/modules/metamodels/templates',
	'be_dcastylepicker'     => 'system/modules/metamodels/templates',
	'be_detectedproblems'   => 'system/modules/metamodels/templates',
	'be_metamodel_full'     => 'system/modules/metamodels/templates',
	'be_subdca'             => 'system/modules/metamodels/templates',
	'ce_metamodel_list'     => 'system/modules/metamodels/templates',
	'metamodel_prerendered' => 'system/modules/metamodels/templates',
	'metamodel_unrendered'  => 'system/modules/metamodels/templates',
	'mod_metamodel_list'    => 'system/modules/metamodels/templates',
));
