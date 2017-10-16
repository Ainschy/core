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

namespace MetaModels\Filter\Setting;

use Doctrine\DBAL\Connection;
use MetaModels\Filter\Setting\Events\CreateFilterSettingFactoryEvent;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the filter settings factory interface.
 */
class FilterSettingFactory implements IFilterSettingFactory
{
    /**
     * The event dispatcher.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $database;

    /**
     * The registered type factories.
     *
     * @var IFilterSettingTypeFactory[]
     */
    protected $typeFactories;

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
     * {@inheritdoc}
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

        if ($this->eventDispatcher->hasListeners(MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE)) {
            @trigger_error(
                'Event "' .
                MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE .
                '" is deprecated - register your attribute factories via the service container.',
                E_USER_DEPRECATED
            );
            $this->serviceContainer->getEventDispatcher()->dispatch(
                MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE,
                new CreateFilterSettingFactoryEvent($this)
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @throws \RuntimeException When the type is already registered.
     */
    public function addTypeFactory($factory)
    {
        $typeName = $factory->getTypeName();
        if (isset($this->typeFactories[$typeName])) {
            throw new \RuntimeException('Filter type ' . $typeName . ' is already registered.');
        }
        $this->typeFactories[$typeName] = $factory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeFactory($type)
    {
        return isset($this->typeFactories[$type]) ? $this->typeFactories[$type] : null;
    }

    /**
     * Create a new setting.
     *
     * @param array       $dbResult       The information from which to initialize the setting from.
     *
     * @param ICollection $filterSettings The MetaModel filter settings.
     *
     * @return ISimple|null
     */
    private function createSetting($dbResult, $filterSettings)
    {
        $factory = $this->getTypeFactory($dbResult['type']);
        if ($factory) {
            $setting = $factory->createInstance($dbResult, $filterSettings);

            if (!$setting) {
                return null;
            }

            // Collect next level.
            if ($factory->isNestedType()) {
                /** @var IWithChildren $setting */
                $this->collectRulesFor($setting, $filterSettings);
            }

            return $setting;
        }

        return null;
    }

    /**
     * Fetch all child rules for the given setting.
     *
     * @param IWithChildren $parentSetting  The information from which to initialize the setting from.
     *
     * @param ICollection   $filterSettings The filter setting instance.
     *
     * @return void
     */
    private function collectRulesFor($parentSetting, $filterSettings)
    {
        // TODO: we should provide a collector like for attributes.
        $childInformation = $this->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_filtersetting')
            ->where('pid=:pid')
            ->andWhere('enabled=1')
            ->orderBy('sorting', 'ASC')
            ->setParameter('pid', $parentSetting->get('id'))
            ->execute();

        foreach ($childInformation->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $childSetting = $this->createSetting($item, $filterSettings);
            if ($childSetting) {
                $parentSetting->addChild($childSetting);
            }
        }
    }

    /**
     * Collect the rules for a filter setting.
     *
     * @param Collection $filterSettings The filter settings instance.
     *
     * @return void
     */
    public function collectRules($filterSettings)
    {
        // TODO: we should provide a collector like for attributes.
        $information = $this->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_filtersetting')
            ->where('fid=:fid')
            ->andWhere('pid=0')
            ->andWhere('enabled=1')
            ->setMaxResults(1)
            ->setParameter('fid', $filterSettings->get('id'))
            ->execute();

        foreach ($information->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $newSetting = $this->createSetting($item, $filterSettings);
            if ($newSetting) {
                $filterSettings->addSetting($newSetting);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException When the MetaModel could not be retrieved.
     */
    public function createCollection($settingId)
    {
        // TODO: we should provide a collector like for attributes.
        $query = $this->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_filter')
            ->where('id=:id')
            ->setMaxResults(1)
            ->setParameter('id', $settingId)
            ->execute();
        if (!$query) {
            throw new \RuntimeException('Could not retrieve filter setting');
        }

        if (!empty($information = $query->fetch(\PDO::FETCH_ASSOC))) {
            $modelFactory = $this->serviceContainer->getFactory();
            $metaModel    = $modelFactory->getMetaModel($modelFactory->translateIdToMetaModelName($information['pid']));
            $collection   = new Collection($information);

            if ($metaModel === null) {
                throw new \RuntimeException('Could not retrieve MetaModel ' . $information['pid']);
            }

            $collection->setMetaModel($metaModel);
            $this->collectRules($collection);

            return $collection;
        }

        return new Collection(array());
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeNames()
    {
        return array_keys($this->typeFactories);
    }
}
