<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Render\Setting;

use Doctrine\DBAL\Connection;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;
use MetaModels\Render\Setting\Events\CreateRenderSettingFactoryEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the filter settings factory interface.
 */
class RenderSettingFactory implements IRenderSettingFactory
{
    /**
     * The event dispatcher.
     *
     * @var IMetaModelsServiceContainer
     *
     * @deprecated The service container will get removed.
     */
    private $serviceContainer;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $database;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * The already created render settings.
     *
     * @var ICollection[]
     */
    private $renderSettings;

    /**
     * Create a new instance.
     *
     * @param Connection               $database
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher to use.
     */
    public function __construct(Connection $database, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->database        = $database;
    }

    /**
     * Set the service container.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container to use.
     *
     * @return RenderSettingFactory
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer, $deprecationNotice = true)
    {
        if ($deprecationNotice) {
            @trigger_error(
                '"' .__METHOD__ . '" is deprecated and will get removed.',
                E_USER_DEPRECATED
            );
        }
        $this->serviceContainer = $serviceContainer;

        if ($this->eventDispatcher->hasListeners(MetaModelsEvents::RENDER_SETTING_FACTORY_CREATE)) {
            @trigger_error(
                'Event "' .
                MetaModelsEvents::RENDER_SETTING_FACTORY_CREATE .
                '" is deprecated - register your factories via the service container.',
                E_USER_DEPRECATED
            );

            $this->serviceContainer->getEventDispatcher()->dispatch(
                MetaModelsEvents::RENDER_SETTING_FACTORY_CREATE,
                new CreateRenderSettingFactoryEvent($this)
            );
        }

        return $this;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer()
    {
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated - use the services from the service container.',
            E_USER_DEPRECATED
        );
        return $this->serviceContainer;
    }

    /**
     * Collect the attribute settings for the given render setting.
     *
     * @param IMetaModel  $metaModel     The MetaModel instance to retrieve the settings for.
     *
     * @param ICollection $renderSetting The render setting.
     *
     * @return void
     */
    public function collectAttributeSettings(IMetaModel $metaModel, $renderSetting)
    {
        $attributeRows = $this
            ->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_rendersetting')
            ->where('pid=:pid')
            ->andWhere('enabled=1')
            ->orderBy('sorting')
            ->setParameter('pid', $renderSetting->get('id'))
            ->execute();

        foreach ($attributeRows->fetchAll(\PDO::FETCH_ASSOC) as $attributeRow) {
            $attribute = $metaModel->getAttributeById($attributeRow['attr_id']);
            if (!$attribute) {
                continue;
            }

            // TODO: we should provide attribute type based render setting elements in version 2.X.
            $attributeSetting = $renderSetting->getSetting($attribute->getColName());
            if (!$attributeSetting) {
                $attributeSetting = $attribute->getDefaultRenderSettings();
            }

            foreach ($attributeRow as $strKey => $varValue) {
                if ($varValue) {
                    $attributeSetting->set($strKey, deserialize($varValue));
                }
            }
            $renderSetting->setSetting($attribute->getColName(), $attributeSetting);
        }
    }

    /**
     * Create a ICollection instance from the id.
     *
     * @param IMetaModel $metaModel The MetaModel for which to retrieve the render setting.
     *
     * @param string     $settingId The id of the ICollection.
     *
     * @return ICollection The instance or null if not found.
     */
    protected function internalCreateRenderSetting(IMetaModel $metaModel, $settingId)
    {
        $row = $this
            ->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_rendersettings')
            ->where('pid=:pid')
            ->andWhere('(id=:id OR isdefault=1)')
            ->orderBy('isdefault')
            ->setParameter('pid', $metaModel->get('id'))
            ->setParameter('id', $settingId ?: 0)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $row = null;
        }

        $renderSetting = new Collection($metaModel, $row);

        if ($renderSetting->get('id')) {
            $this->collectAttributeSettings($metaModel, $renderSetting);
        }

        return $renderSetting;
    }

    /**
     * {@inheritdoc}
     */
    public function createCollection(IMetaModel $metaModel, $settingId = '')
    {
        $tableName = $metaModel->getTableName();
        if (!isset($this->renderSettings[$tableName])) {
            $this->renderSettings[$tableName] = array();
        }

        if (!isset($this->renderSettings[$tableName][$settingId])) {
            $this->renderSettings[$tableName][$settingId] = $this->internalCreateRenderSetting($metaModel, $settingId);
        }

        return $this->renderSettings[$tableName][$settingId];
    }
}
