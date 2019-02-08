<?php
/**
Ресайз изображений разными графическими библиотеками
 */
namespace Mf\Storage\Filter;


class ImgOptimize  extends \Mf\Imglib\Filter\ImgOptimize
{
	
public function __construct($options = array())
	 {
        trigger_error("Измените конфигурацию вашего приложения, в пространстве имен Mf\Storage замените на Mf\Imglib все что касается изображений", E_USER_NOTICE);
		$this->setOptions($options);
	}
	
}