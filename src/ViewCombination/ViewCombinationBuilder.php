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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\ViewCombination;

use Doctrine\DBAL\Connection;
use MetaModels\IFactory;

/**
 * This builds the view combinations for an user.
 */
class ViewCombinationBuilder
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     * @param IFactory   $factory    The MetaModels factory.
     */
    public function __construct(Connection $connection, IFactory $factory)
    {
        $this->connection = $connection;
        $this->factory    = $factory;
    }

    /**
     * Retrieve the combinations for the passed user.
     *
     * @param string[] $userGroups The user groups.
     * @param string   $userType   The user type ('fe' or 'be').
     *
     * @return array|null
     */
    public function getCombinationsForUser($userGroups, $userType)
    {
        $userType = strtolower($userType);
        if ('fe' !== $userType && 'be' !== $userType) {
            throw new \InvalidArgumentException('Unknown user type: ' . $userType);
        }

        return $this->getCombinationsFromDatabase($userGroups, $userType);
    }

    /**
     * Retrieve the palette combinations from the database.
     *
     * @param string $userGroups The user groups of the user to fetch information for.
     * @param string $userType   The user type.
     *
     * @return null|array
     */
    private function getCombinationsFromDatabase($userGroups, $userType)
    {
        if (empty($userGroups)) {
            return null;
        }

        $builder = $this
            ->connection
            ->createQueryBuilder();

        $combinations = $builder
            ->select('*')
            ->from('tl_metamodel_dca_combine')
            ->where($builder->expr()->in($userType . '_group', ':groupList'))
            ->setParameter('groupList', $userGroups, Connection::PARAM_STR_ARRAY)
            ->orWhere($userType . '_group=0')
            ->orderBy('pid')
            ->addOrderBy('sorting')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result = [
            'byName' => [],
            'byId' => []
        ];

        foreach ($combinations as $combination) {
            $metaModelId = $combination['pid'];
            if (isset($result['byId'][$metaModelId])) {
                continue;
            }
            $name = $this->factory->translateIdToMetaModelName($metaModelId);

            $result['byId'][$metaModelId] = $result['byName'][$name] = [
                'dca_id'   => $combination['dca_id'],
                'view_id' => $combination['view_id']
            ];
        }
        $this->addDefaultInputScreens($result);
        $this->addDefaultRenderSettings($result);

        return $result;
    }

    /**
     * Get the default input screens (if any defined).
     *
     * @param array $result The result so far.
     *
     * @return void
     */
    private function addDefaultInputScreens(&$result)
    {
        $builder = $this->connection->createQueryBuilder();

        $combinations = $builder
            ->select('*')
            ->from('tl_metamodel_dca')
            ->where('isdefault=1')
            ->andWhere($builder->expr()->notIn('pid', ':idList'))
            ->setParameter('idList', array_keys($result['byId']), Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($combinations as $combination) {
            $metaModelId = $combination['pid'];
            if (isset($result['byId'][$metaModelId]['dca_id']) && !empty($result['byId'][$metaModelId]['dca_id'])) {
                continue;
            }
            $name = $this->factory->translateIdToMetaModelName($metaModelId);

            $result['byId'][$metaModelId]['dca_id'] = $result['byName'][$name]['dca_id'] = $combination['id'];
        }
    }

    /**
     * Get the default input screens (if any defined).
     *
     * @param array $result The result so far.
     *
     * @return void
     */
    private function addDefaultRenderSettings(&$result)
    {
        $builder = $this->connection->createQueryBuilder();

        $combinations = $builder
            ->select('*')
            ->from('tl_metamodel_rendersettings')
            ->where('isdefault=1')
            ->andWhere($builder->expr()->notIn('pid', ':idList'))
            ->setParameter('idList', array_keys($result['byId']), Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($combinations as $combination) {
            $metaModelId = $combination['pid'];
            if (isset($result['byId'][$metaModelId]['view_id']) && !empty($result['byId'][$metaModelId]['view_id'])) {
                continue;
            }
            $name = $this->factory->translateIdToMetaModelName($metaModelId);

            $result['byId'][$metaModelId]['view_id'] = $result['byName'][$name]['view_id'] = $combination['id'];
        }
    }
}
