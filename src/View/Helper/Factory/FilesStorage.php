<?php
namespace Storage\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Storage\Service\FilesLib;

/**
 * универсальная фабрика для помощника
 * 
 */
class FilesStorage implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
	   $FilesLib=$container->get(FilesLib::class);
        return new $requestedName($FilesLib);
    }
}

