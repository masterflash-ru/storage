<?php

namespace Mf\Storage\Controller;

use Interop\Container\ContainerInterface;
use Mf\Storage\Service\ImagesLib;


class IndexControllerFactory
{
    public function __invoke(ContainerInterface $container,$requestedName)
    {
        $ImagesLib=$container->get(ImagesLib::class);
        return new $requestedName($ImagesLib);
    }
}