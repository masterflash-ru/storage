<?php
namespace Mf\Storage\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mf\Storage\Service\ImagesLib;

/**
 * универсальная фабрика для помощника
 * 
 */
class ImageStorage implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
	   $ImagesLib=$container->get(ImagesLib::class);
        return new $requestedName($ImagesLib);
    }
}

