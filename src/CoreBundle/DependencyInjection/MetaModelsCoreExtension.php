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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\DependencyInjection;

use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages the bundle configuration
 */
class MetaModelsCoreExtension extends Extension
{
    /**
     * The configuration files.
     *
     * @var string[]
     */
    private static $files = [
        'config.yml',
        'filter-settings.yml',
        'hooks.yml',
        'listeners.yml',
        'services.yml',
        'dc-general/breadcrumb.yml',
        'dc-general/definition-builder.yml',
        'dc-general/environment-populator.yml',
        'dc-general/listener.yml',
        'dc-general/table/tl_attribute.yml',
        'dc-general/table/tl_dca.yml',
        'dc-general/table/tl_dca_combine.yml',
        'dc-general/table/tl_dca_sortgroup.yml',
        'dc-general/table/tl_dcasetting.yml',
        'dc-general/table/tl_dcasetting_condition.yml',
        'dc-general/table/tl_filtersetting.yml',
        'dc-general/table/tl_metamodel.yml',
        'dc-general/table/tl_metamodel_rendersetting.yml',
        'dc-general/table/tl_metamodel_rendersettings.yml',
        'dc-general/table/tl_metamodel_searchable_pages.yml',
    ];

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        foreach (self::$files as $file) {
            $loader->load($file);
        }

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $this->buildCacheService($container, $config);

        $container->setParameter('metamodels.resource_dir', __DIR__ . '/../Resources');
        $container->setParameter('metamodels.assets_dir', $config['assets_dir']);
        $container->setParameter('metamodels.assets_web', $config['assets_web']);

        $this->buildPickerService($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration(
            $container->getParameter('kernel.debug'),
            $container->getParameter('kernel.project_dir')
        );
    }

    /**
     * Build the cache service.
     *
     * @param ContainerBuilder $container The container builder.
     * @param array            $config    The configuration.
     *
     * @return void
     */
    private function buildCacheService(ContainerBuilder $container, array $config)
    {
        // if cache disabled, swap it out with the dummy cache.
        if (!$config['enable_cache']) {
            $cache = $container->getDefinition('metamodels.cache');
            $cache->setClass(ArrayCache::class);
            $cache->setArguments([]);
            $container->setParameter('metamodels.cache_dir', null);
            return;
        }

        $container->setParameter('metamodels.cache_dir', $config['cache_dir']);
    }

    /**
     * Build the picker service.
     *
     * @param ContainerBuilder $container The container.
     *
     * @return void
     */
    private function buildPickerService(ContainerBuilder $container)
    {
        $pickerService = $container->getDefinition('metamodels.controller.picker');
        $configs       = $pickerService->getArgument(2);

        // Selectable styles in the palette tl_class definitions.
        $configs['PALETTE_STYLE_PICKER'][] = ['cssclass' => 'w50'];
        $configs['PALETTE_STYLE_PICKER'][] = ['cssclass' => 'w50x'];
        $configs['PALETTE_STYLE_PICKER'][] = ['cssclass' => 'clr'];
        $configs['PALETTE_STYLE_PICKER'][] = ['cssclass' => 'clx'];
        $configs['PALETTE_STYLE_PICKER'][] = ['cssclass' => 'long'];
        $configs['PALETTE_STYLE_PICKER'][] = ['cssclass' => 'wizard'];
        $configs['PALETTE_STYLE_PICKER'][] = ['cssclass' => 'm12'];
        // Selectable panels in the palette panelLayout definitions.
        $configs['PALETTE_PANEL_PICKER'][] = ['cssclass' => 'search'];
        $configs['PALETTE_PANEL_PICKER'][] = ['cssclass' => 'sort'];
        $configs['PALETTE_PANEL_PICKER'][] = ['cssclass' => 'filter'];
        $configs['PALETTE_PANEL_PICKER'][] = ['cssclass' => 'limit'];

        $pickerService->setArgument(2, $configs);
    }
}
