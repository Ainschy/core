<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test;

use MetaModels\Events\CreateMetaModelEvent;
use MetaModels\Events\GetMetaModelNameFromIdEvent;
use MetaModels\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the attribute factory.
 *
 * @covers \MetaModels\Factory
 */
class FactoryTest extends TestCase
{
    /**
     * Test factory dispatches the create event.
     *
     * @return void
     */
    public function testCreateMetaModelFiresEvent()
    {
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMockForAbstractClass();
        $factory    = new Factory($dispatcher);

        $dispatcher
            ->expects(self::exactly(1))
            ->method('dispatch')
            ->with(self::equalTo(CreateMetaModelEvent::NAME));

        $factory->getMetaModel('mm_test');
    }

    /**
     * Test translating an id to metamodel name.
     *
     * @return void
     */
    public function testGetMetaModelNameFromId()
    {
        $dispatcher = new EventDispatcher();
        $factory    = new Factory($dispatcher);

        $dispatcher->addListener(
            GetMetaModelNameFromIdEvent::NAME,
            function (GetMetaModelNameFromIdEvent $event) {
                $event->setMetaModelName('mm_with_id_' . $event->getMetaModelId());
            }
        );

        self::assertSame('mm_with_id_10', $factory->translateIdToMetaModelName(10));

        $factory->getMetaModel('mm_test');
    }
}
