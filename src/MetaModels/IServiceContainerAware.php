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

namespace MetaModels;

/**
 * Reference implementation of IMetaModelsServiceContainer.
 */
interface IServiceContainerAware
{
    /**
     * Set the service container to use.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container.
     *
     * @return IServiceContainerAware
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer);

    /**
     * Retrieve the service container in use.
     *
     * @return IMetaModelsServiceContainer|null
     */
    public function getServiceContainer();
}
