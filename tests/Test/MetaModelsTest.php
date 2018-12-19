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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModel;
use MetaModels\MetaModelsServiceContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the base attribute.
 *
 * @covers \MetaModels\MetaModel
 */
class MetaModelsTest extends TestCase
{
    /**
     * Mock a service container with the given database.
     *
     * @param \Contao\Database $database The contao database instance.
     *
     * @return IMetaModelsServiceContainer
     */
    protected function mockServiceContainer($database)
    {
        $serviceContainer = new MetaModelsServiceContainer();
        $serviceContainer->setDatabase($database);

        return $serviceContainer;
    }

    /**
     * Test instantiation of a MetaModel.
     *
     * @return void
     */
    public function testCreation()
    {
        $values = array(
            'id'         => '1',
            'sorting'    => '1',
            'tstamp'     => '0',
            'name'       => 'MetaModel',
            'tableName'  => 'mm_test',
            'mode'       => '',
            'translated' => '1',
            'languages'  => array(
                'en' => array('isfallback' => '1'),
                'de' => array('isfallback' => '')
            ),
            'varsupport' => '1',
        );

        $serialized = array();
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $serialized[$key] = serialize($value);
            } else {
                $serialized[$key] = $value;
            }
        }

        $metaModel = new MetaModel($serialized);
        $this->assertEmpty($metaModel->getAttributes());

        foreach ($values as $key => $value) {
            $this->assertEquals($value, $metaModel->get($key), $key);
        }

        $metaModel = new MetaModel($values);

        foreach ($values as $key => $value) {
            $this->assertEquals($value, $metaModel->get($key), $key);
        }
    }

    /**
     * Test method MetaModel::fetchRows.
     *
     * @return void
     */
    public function testFetchRows()
    {
        $this->markTestIncomplete('We need to rewrite MetaModel to utilize doctrine first');

        $metaModel = new MetaModel(array(
            'id'         => '1',
            'sorting'    => '1',
            'tstamp'     => '0',
            'name'       => 'MetaModel',
            'tableName'  => 'mm_test',
            'mode'       => '',
            'translated' => '1',
            'languages'  => array(
                'en' => array('isfallback' => '1'),
                'de' => array('isfallback' => '')
            ),
            'varsupport' => '1',
        ));

        $rows = array(
            1 => array(
                'id'     => 1,
                'tstamp' => 343094400,
            ),
            2 => array(
                'id'     => 2,
                'tstamp' => 343094400,
            ),
        );

        $database = Database::getNewTestInstance();
        $metaModel->setServiceContainer($this->mockServiceContainer($database));

        $database
            ->getQueryCollection()
            ->theQuery('SELECT * FROM mm_test WHERE id IN (?,?) ORDER BY FIELD(id,?,?)')
            ->with(1, 2, 1, 2)
            ->result()
                ->addRows($rows);

        $reflection = new \ReflectionMethod($metaModel, 'fetchRows');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($metaModel, array(1,2));

        $this->assertEquals($result, $rows);
    }

    /**
     * Ensure the buildDatabaseParameterList works correctly.
     *
     * @return void
     */
    public function testBuildDatabaseParameterList()
    {
        $metaModel = new MetaModel(array());

        $reflection = new \ReflectionMethod($metaModel, 'buildDatabaseParameterList');
        $reflection->setAccessible(true);
        $this->assertEquals('?', $reflection->invoke($metaModel, array(1)));
        $this->assertEquals('?,?', $reflection->invoke($metaModel, array(1,2)));
        $this->assertEquals('?,?,?,?,?,?', $reflection->invoke($metaModel, array(1, 2, 'fooo', 'bar', null, 'test')));
    }

    /**
     * Ensure the system columns are present. See issue #196.
     *
     * @return void
     */
    public function testRetrieveSystemColumns()
    {
        $this->markTestIncomplete('We need to rewrite MetaModel to utilize doctrine first');

        $metaModel = new MetaModel(array(
            'id'         => '1',
            'sorting'    => '256',
            'tstamp'     => '1367274071',
            'name'       => 'Test RetrieveSystemColumns',
            'tableName'  => 'mm_test_retrieve',
            'translated' => '1',
            'languages'  => serialize(array('en' => array('isfallback' => '1'), 'de' => array('isfallback' => '0'))),
            'varsupport' => '',
        ));

        $rows = array(
            1 => array(
                'id'      => 1,
                'pid'     => 0,
                'sorting' => 1,
                'tstamp'  => 343094400,
            ),
        );

        $database = Database::getNewTestInstance();
        $metaModel->setServiceContainer($this->mockServiceContainer($database));

        $database
            ->getQueryCollection()
            ->theQuery('SELECT * FROM mm_test_retrieve WHERE id IN (?) ORDER BY FIELD(id,?)')
            ->with(1, 1)
            ->result()
            ->addRows($rows);

        $this->assertEquals($metaModel->getName(), 'Test RetrieveSystemColumns');

        $item = $metaModel->findById(1);

        $this->assertEquals(1, $item->get('id'));
        $this->assertEquals(0, $item->get('pid'));
        $this->assertEquals(1, $item->get('sorting'));
        $this->assertEquals(343094400, $item->get('tstamp'));
        $this->assertNull($item->get('varbase'));
        $this->assertNull($item->get('vargroup'));
    }

    /**
     * Ensure the buildDatabaseParameterList works correctly.
     *
     * @return void
     */
    public function testGetIdsFromFilter()
    {
        $this->markTestIncomplete('We need to rewrite MetaModel to utilize doctrine first');

        $metaModel = $this
            ->getMockBuilder('MetaModels\MetaModel')
            ->setMethods(array('getMatchingIds'))
            ->setConstructorArgs(array(array('tableName'  => 'mm_test_retrieve')))
            ->getMock();
        $metaModel
            ->expects($this->any())
            ->method('getMatchingIds')
            ->will($this->returnValue(array(4, 3, 2, 1)));

        /** @var MetaModel $metaModel */
        $database = Database::getNewTestInstance();
        $metaModel->setServiceContainer($this->mockServiceContainer($database));

        $database
            ->getQueryCollection()
            ->theQuery('SELECT id FROM mm_test_retrieve WHERE id IN(?,?,?,?) ORDER BY id ASC')
            ->with(4, 3, 2, 1)
            ->result()
            ->addRow(array('id' => 1))
            ->addRow(array('id' => 2))
            ->addRow(array('id' => 3))
            ->addRow(array('id' => 4));

        $this->assertEquals(array(1,2,3,4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id'));
        $this->assertEquals(array(1,2), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 0, 2));
        $this->assertEquals(array(3,4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 2, 2));
        $this->assertEquals(array(3), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 2, 1));
        $this->assertEquals(array(), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 20, 0));
        $this->assertEquals(array(2,3,4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 1, 10));
    }

    /**
     * Ensure the getCount works correctly.
     *
     * @return void
     */
    public function testGetCountForEmptyList()
    {
        $metaModel = $this
            ->getMockBuilder(MetaModel::class)
            ->setMethods(['getMatchingIds'])
            ->setConstructorArgs(
                [
                    ['tableName' => 'mm_test_retrieve'],
                    $this->getMockForAbstractClass(EventDispatcherInterface::class),
                    $this->mockConnection([])
                ]
            )
            ->getMock();
        $metaModel
            ->expects($this->once())
            ->method('getMatchingIds')
            ->willReturn([]);

        /** @var MetaModel $metaModel */
        $this->assertEquals(0, $metaModel->getCount($metaModel->getEmptyFilter()));
    }

    /**
     * Ensure the getCount works correctly.
     *
     * @return void
     */
    public function testGetCountForNonEmptyList()
    {
        $metaModel = $this->getMockBuilder(MetaModel::class)
            ->setMethods(['getMatchingIds'])
            ->setConstructorArgs([
                ['tableName' => 'mm_test_retrieve'],
                $this->getMockForAbstractClass(EventDispatcherInterface::class),
                $this->mockConnection([
                    \Closure::fromCallable(function () {
                        $builder = $this
                            ->getMockBuilder(QueryBuilder::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                        $builder
                            ->expects($this->once())
                            ->method('select')
                            ->with('COUNT(id)')
                            ->willReturn($builder);
                        $builder
                            ->expects($this->once())
                            ->method('from')
                            ->with('mm_test_retrieve')
                            ->willReturn($builder);

                        $expr = $this
                            ->getMockBuilder(ExpressionBuilder::class)
                            ->disableOriginalConstructor()
                            ->setMethods()
                            ->getMock();

                        $builder
                            ->expects($this->once())
                            ->method('expr')
                            ->willReturn($expr);

                        $builder
                            ->expects($this->once())
                            ->method('where')
                            ->with('id IN (:values)')
                            ->willReturn($builder);

                        $builder
                            ->expects($this->once())
                            ->method('setParameter')
                            ->with('values', [4, 3, 2, 1], Connection::PARAM_STR_ARRAY)
                            ->willReturn($builder);

                        $statement = $this
                            ->getMockBuilder(Statement::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                        $statement
                            ->expects($this->once())
                            ->method('fetch')
                            ->with(\PDO::FETCH_COLUMN)
                            ->willReturn(4);
                        $builder
                            ->expects($this->once())
                            ->method('execute')
                            ->willReturn($statement);

                        return $builder;
                    })->__invoke()
                ])
            ])
            ->getMock();
        $metaModel
            ->expects($this->once())
            ->method('getMatchingIds')
            ->willReturn([4, 3, 2, 1]);

        /** @var MetaModel $metaModel */
        $this->assertEquals(4, $metaModel->getCount($metaModel->getEmptyFilter()));
    }

    /**
     * Mock a database connection with hte passed query builders.
     *
     * @param array $queryBuilders The query builder list.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockConnection(array $queryBuilders)
    {
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        if ([] !== $queryBuilders) {
            $connection
                ->expects($this->exactly(count($queryBuilders)))
                ->method('createQueryBuilder')
                ->willReturnOnConsecutiveCalls(...$queryBuilders);
        } else {
            $connection
                ->expects($this->never())
                ->method('createQueryBuilder');
        }

        return $connection;
    }
}
