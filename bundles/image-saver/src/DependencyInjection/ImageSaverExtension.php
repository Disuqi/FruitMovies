<?php

namespace AWD\ImageSaver\DependencyInjection;

use AWD\ImageSaver\ImageSaver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ImageSaverExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container,  new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $config = $this->processConfiguration(new Configuration(), $configs);
        $imageSaver = $container->getDefinition(ImageSaver::class);
        $imageSaver->setArgument('$entities', $config["entities"]);
        $imageSaver->setArgument('$handleEntityManager', $config["handle_entity_manager"]);
    }
}