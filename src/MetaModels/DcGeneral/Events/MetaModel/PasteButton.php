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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\DcGeneral\Events\BaseSubscriber;

/**
 * This class handles the paste into and after button activation and deactivation for all MetaModels being edited.
 *
 * @package MetaModels\DcGeneral\Events\MetaModel
 */
class PasteButton extends BaseSubscriber
{
    /**
     * The current environment.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * The current data provider.
     *
     * @var DataProviderInterface
     */
    protected $provider;

    /**
     * The name of current data provider.
     *
     * @var string
     */
    protected $providerName;

    /**
     * The model where we have to check if
     * is it a paste into or paste after.
     *
     * @var ModelInterface
     */
    protected $currentModel;

    /**
     * Get determinator if there exists a circular reference.
     *
     * This flag determines if there exists a circular reference between the item currently in the clipboard and the
     * current model. A circular reference is of relevance when performing a cut and paste operation for example.
     *
     * @var boolean
     */
    protected $circularReference;

    /**
     * Disable the paste after.
     *
     * @var bool
     */
    protected $disablePA = true;

    /**
     * Disable the paste into.
     *
     * @var bool
     */
    protected $disablePI = true;

    /**
     * Register all listeners.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $this->addListener(
            GetPasteButtonEvent::NAME,
            array($this, 'handle')
        );

        $this->addListener(
            GetPasteRootButtonEvent::NAME,
            array($this, 'handleRoot')
        );
    }

    /**
     * Find a item by his id.
     *
     * @param int $id The id to find.
     *
     * @return ModelInterface
     */
    protected function getModelById($id)
    {
        if ($id === null) {
            return null;
        }

        $provider = $this->environment->getDataProvider();
        $config   = $provider
            ->getEmptyConfig()
            ->setId($id);

        return $provider->fetch($config);
    }

    /**
     * Determines if this MetaModel instance is subject to variant handling.
     *
     * @return bool true if variants are handled, false otherwise.
     */
    protected function hasVariants()
    {
        $metaModels = $this
            ->getServiceContainer()
            ->getFactory()
            ->getMetaModel($this->providerName);

        if ($metaModels === null) {
            throw new \RuntimeException(sprintf('Could not find a MetaModels with the name %s', $this->providerName));
        }

        return $metaModels->hasVariants();
    }

    /**
     * Handle the paste into and after buttons.
     *
     * @param GetPasteRootButtonEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When more than one model is contained within the clipboard.
     */
    public function handleRoot(GetPasteRootButtonEvent $event)
    {
        $this->environment  = $event->getEnvironment();
        $this->provider     = $this->environment->getDataProvider();
        $this->providerName = $this->provider->getEmptyModel()->getProviderName();
        $clipboard          = $this->environment->getClipboard();
        $this->currentModel = null;
        $this->disablePI    = false;

        // Only run for a MetaModels.
        if ((substr($this->providerName, 0, 3) !== 'mm_') || $event->isPasteDisabled()) {
            return;
        }

        $this->checkForAction($clipboard, 'create');
        $this->checkForAction($clipboard, 'cut');

        $event->setPasteDisabled($this->disablePI);
    }

    /**
     * Handle the paste into and after buttons.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When more than one model is contained within the clipboard.
     */
    public function handle(GetPasteButtonEvent $event)
    {
        $this->circularReference = $event->isCircularReference();
        $this->environment       = $event->getEnvironment();
        $this->provider          = $this->environment->getDataProvider();
        $this->providerName      = $this->provider->getEmptyModel()->getProviderName();
        $clipboard               = $this->environment->getClipboard();
        $this->currentModel      = $event->getModel();
        $this->disablePI         = true;
        $this->disablePA         = true;

        // Only run for a MetaModels and if both values already disabled return here.
        if ((substr($this->providerName, 0, 3) !== 'mm_')
            || ($event->isPasteIntoDisabled() && $event->isPasteAfterDisabled())
        ) {
            return;
        }

        $this->checkForAction($clipboard, 'create');
        $this->checkForAction($clipboard, 'cut');

        $event
            ->setPasteAfterDisabled($this->disablePA)
            ->setPasteIntoDisabled($this->disablePI);
    }

    /**
     * Check the buttons based on the action.
     *
     * @param ClipboardInterface $clipboard The clipboard.
     *
     * @param string             $action    The action to be checked.
     */
    protected function checkForAction($clipboard, $action)
    {
        // Make a filter for the given action.
        $filter = new Filter();
        $filter->andActionIs($action);
        $items = $clipboard->fetch($filter);

        // Check if there are items.
        if ($items === null) {
            return;
        }

        /** @var ItemInterface[] $items */
        foreach ($items as $item) {
            // Check the context.
            $itemProviderName = $item->getModelId()->getDataProviderName();
            $modelId          = $item->getModelId()->getId();
            if ($this->providerName !== $itemProviderName) {
                continue;
            }

            $containedModel = $this->getModelById($modelId);
            if ($this->currentModel == null) {
                $this->checkForRoot($containedModel, $action);
            } elseif ($containedModel) {
                $this->checkForModel($containedModel, $action);
            } else {
                $this->checkEmpty($action);
            }
        }
    }

    /**
     * Check the PA and PI without a contained model.
     *
     * @param string $action The action to be checked.
     */
    protected function checkEmpty($action)
    {
        if ($this->hasVariants() && $this->currentModel !== null) {
            $this->disablePA = false;
        } elseif ($action == 'create') {
            $this->disablePA = false;
            $this->disablePI = false;
        }
    }

    /**
     * Check the PI for the root element.
     *
     * @param ModelInterface $containedModel The model with all data.
     *
     * @param string         $action         The action to be checked.
     */
    protected function checkForRoot($containedModel, $action)
    {
        if ($this->hasVariants() && $action == 'cut' && $containedModel->getProperty('varbase') == 0) {
            $this->disablePI = true;
        }
    }

    /**
     * Check the PA and PI with a model.
     *
     * @param ModelInterface $containedModel The model with all data.
     *
     * @param string         $action         The action to be checked.
     */
    protected function checkForModel($containedModel, $action)
    {
        if (!$this->circularReference && $this->hasVariants()) {
            $this->checkModelWithVariants($containedModel);
        } elseif (!$this->circularReference && !$this->hasVariants()) {
            $this->checkModelWithoutVariants($containedModel);
        } elseif ($this->currentModel == null && $containedModel->getProperty('varbase') == 0) {
            $this->disablePA = true;
        } else {
            $this->disablePA = false;
            // The following rules apply:
            // 1. Variant bases must not get pasted into anything.
            // 2. If we are not in create mode, disable the paste into for the item itself.
            $this->disablePI =
                ($this->hasVariants() && $containedModel->getProperty('varbase') == 1)
                || ($action != 'create' && $containedModel->getId() == $this->currentModel->getId());
        }
    }

    /**
     * Check the PA and PI with a model and variant support.
     *
     * @param ModelInterface $containedModel
     */
    protected function checkModelWithVariants($containedModel)
    {
        // Item and variant support.
        $isVarbase        = (bool)$containedModel->getProperty('varbase');
        $vargroup         = $containedModel->getProperty('vargroup');
        $isCurrentVarbase = (bool)$this->currentModel->getProperty('varbase');
        $currentVargroup  = $this->currentModel->getProperty('vargroup');

        if ($isVarbase && !$this->circularReference && $isCurrentVarbase) {
            // Insert new items only after bases.
            // Insert a varbase after any other varbase, for sorting.
            $this->disablePA = false;
        } elseif (!$isVarbase && !$isCurrentVarbase && $vargroup == $currentVargroup) {
            // Move items in their vargroup and only there.
            $this->disablePA = false;
        }

        $this->disablePI = !$isCurrentVarbase || $isVarbase;
    }

    /**
     * Check the PA and PI with a model and a normal flat build.
     *
     * @param ModelInterface $containedModel
     */
    protected function checkModelWithoutVariants($containedModel)
    {
        $this->disablePA = ($this->currentModel->getId() == $containedModel->getId())
            || ($this->currentModel->getProperty('pid') == $containedModel->getId('pid'));
        $this->disablePI = ($this->circularReference)
            || ($this->currentModel->getId() == $containedModel->getId())
            || ($this->currentModel->getProperty('pid') == $containedModel->getId());
    }
}
