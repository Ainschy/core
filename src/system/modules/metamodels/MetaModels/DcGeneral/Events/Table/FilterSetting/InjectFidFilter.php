<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Draw a filter setting in the backend.
 */
class InjectFidFilter
{
	/**
	 * Add the fid filter for the current filter specified via input provider.
	 *
	 * @param PopulateEnvironmentEvent $event The event.
	 *
	 * @return void
	 */
	public static function handle(PopulateEnvironmentEvent $event)
	{
		$environment   = $event->getEnvironment();
		$input         = $environment->getInputProvider();
		$relationships = $environment->getDataDefinition()->getModelRelationshipDefinition();
		$filterId      = IdSerializer::fromSerialized($input->getParameter('pid'));

		$root     = $relationships->getRootCondition();
		$filter   = $root->getFilterArray();
		$filter[] = array
		(
			'property'    => 'fid',
			'operation'   => '=',
			'value'       => $filterId->getId(),
		);
		$root->setFilterArray($filter);

		$childConditions = $relationships->getChildConditions('tl_metamodel_filtersetting');

		foreach ($childConditions as $childCondition)
		{
			if ($childCondition->getDestinationName() == 'tl_metamodel_filtersetting')
			{
				$filter   = $childCondition->getFilterArray();
				$filter[] = array
				(
					'local'        => 'fid',
					'operation'    => '=',
					'remote_value' => $filterId->getId(),
				);
				$childCondition->setFilterArray($filter);

				$setter   = $childCondition->getSetters();
				$setter[] = array
				(
					'property'    => 'fid',
					'value'       => $filterId->getId(),
				);

				$childCondition->setSetters($setter);
			}
		}
	}
}
