<?php

namespace AWD\ImageSaverBundle;
use AWD\ImageSaverBundle\DependencyInjection\AwdImageSaverExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AwdImageSaverBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new AwdImageSaverExtension();
    }
}