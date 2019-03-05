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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use MetaModels\IMetaModel;

/**
 * This interface describes a property condition factory.
 */
interface PropertyConditionFactoryInterface
{
    /**
     * Create a property condition.
     *
     * @param array      $configuration The configuration.
     * @param IMetaModel $metaModel     The MetaModel the condition relates to.
     *
     * @return PropertyConditionInterface
     */
    public function buildCondition(array $configuration, IMetaModel $metaModel);
}
