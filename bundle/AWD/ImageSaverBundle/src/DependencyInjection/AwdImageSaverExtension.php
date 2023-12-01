<?php

namespace AWD\ImageSaverBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AwdImageSaverExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../../config"));
        $loader->load("services.yaml");

        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter("entities", "hello");
    }
}