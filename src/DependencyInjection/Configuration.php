<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lle_config');
        $rootNode = $treeBuilder->getRootNode();
        $children = $rootNode->children();
        $children
            ->scalarNode('using_tenant')
            ->defaultValue(false)
            ->validate()
                ->ifNotInArray([
                    true,
                    false
                ])
                ->thenInvalid('Invalid alignment value %s')
            ->end();
        $children
            ->scalarNode('tenant_service')
            ->defaultNull()
            ->end();

        return $treeBuilder;
    }
}
