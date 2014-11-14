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

namespace MetaModels;

use MetaModels\Attribute\IAttributeFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Reference implementation of IMetaModelsServiceContainer.
 */
class MetaModelsServiceContainer implements IMetaModelsServiceContainer
{
    /**
     * The factory to use.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * The factory to use.
     *
     * @var IFactory
     */
    protected $attributeFactory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * The Contao database instance to use.
     *
     * @var \Contao\Database
     */
    protected $database;

    /**
     * Registered services.
     *
     * @var object[]
     */
    protected $services;

    /**
     * Set the factory to use.
     *
     * @param IFactory $factory The factory in use.
     *
     * @return MetaModelsServiceContainer
     */
    public function setFactory(IFactory $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFactory()
    {
        return $this->factory;
    }


    /**
     * Set the factory to use.
     *
     * @param IAttributeFactory $factory The factory in use.
     *
     * @return MetaModelsServiceContainer
     */
    public function setAttributeFactory(IAttributeFactory $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeFactory()
    {
        return $this->attributeFactory;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return MetaModelsServiceContainer
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Set the Contao database instance.
     *
     * @param \Contao\Database $database The contao database instance.
     *
     * @return MetaModelsServiceContainer
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * {@inheritdoc}
     */
    public function setService($service, $serviceName = null)
    {
        if ($serviceName === null) {
            $serviceName = get_class($service);
        }

        $this->services[$serviceName] = $service;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getService($serviceName)
    {
        return isset($this->services[(string) $serviceName]) ? $this->services[(string) $serviceName] : null;
    }
}
