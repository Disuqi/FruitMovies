<?php

namespace AWD\ImageSaverBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('awd_image_saver');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->arrayNode('entities')
                ->info("list of entities that can have images")
                ->children()
                    ->arrayNode('example_entity')
                    ->info("name of the entity class")
                    ->children()
                        ->scalarNode('id')->defaultValue("id")->info("name of the id property of entity")->end()
                        ->scalarNode('image')->defaultValue("cover_photo")->info("name of the image property of entity")->end()
                    ->end()
                ->end()
                ->booleanNode('handle_entity_manager')->defaultFalse()->info("whether to handle entity manager")->end()
            ->end() // twitter
            ->end()
        ;

        return $treeBuilder;
    }
}