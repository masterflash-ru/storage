<?php
/**
наложение водного знака на изображение разными графическими библиотеками
 */
namespace Mf\Storage\Filter;



class Watermark  extends \Mf\Imglib\Filter\Watermark
{

	public function __construct($options = array())
	 {
		$this->setOptions($options);
            trigger_error("Измените конфигурацию вашего приложения, в пространстве имен Mf\Storage замените на Mf\Imglib все что касается изображений", E_USER_NOTICE);
	}
	
}