<?php

namespace Msi\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('msi_admin');

        $rootNode
            ->children()
                ->scalarNode('tiny_mce')
                    ->defaultValue('MsiAdminBundle:Form:tiny_mce.html.twig')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('app_locales')
                    ->prototype('scalar')->end()
                    ->defaultValue(array('en', 'fr'))
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('super_admin_routes')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
