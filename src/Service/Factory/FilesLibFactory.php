<?php
namespace Storage\Service\Factory;

use Interop\Container\ContainerInterface;

/*
Фабрика 
генерации сервиса обработки файлов/фото и записи/возврата в хранилище
*/

class FilesLibFactory
{

public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
		$connection=$container->get('ADO\Connection');
       $config=$container->get("config");
	   $cache = $container->get('DefaultSystemCache');
	   if (!empty($config["storage"])) 
	   	{
			$config=$config["storage"];
		}
		else {throw new \Exception("Нет секции 'storage' в конфиге приложения");}
        return new $requestedName($connection,$config,$cache);
    }
}

