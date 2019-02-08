<?php
/*
Абстрактный адаптер
*/

namespace Mf\Storage\Filter\Service;
use Exception;

abstract class ImgAbstract extends \Mf\Imglib\Filter\Adapter\ImgAbstract
{

public function __construct($options = [])
{		
	$this->setOptions($options);
    trigger_error("Измените конфигурацию вашего приложения, в пространстве имен Mf\Storage замените на Mf\Imglib все что касается изображений", E_USER_NOTICE);

}
	
	
	
}
