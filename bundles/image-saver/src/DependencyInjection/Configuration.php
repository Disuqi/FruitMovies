<?php

namespace AWD\ImageSaver\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('image-saver');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->arrayNode("entities")
                    ->arrayPrototype()
                    ->children()
                        ->scalarNode("id")->isRequired()->end()
                        ->scalarNode("image")->isRequired()->end()
                        ->scalarNode("base_dir")->defaultNull()->end()
                    ->end()
                    ->end()
                ->isRequired()
                ->end()
                ->booleanNode("handle_entity_manager")
                ->defaultFalse()
                ->isRequired()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}