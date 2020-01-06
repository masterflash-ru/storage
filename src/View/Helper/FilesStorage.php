<?php
/*
помощник view для получения из хранилища файла, 
возвращается МАССИВ!!!!!!!
*/

namespace Mf\Storage\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * помощник - получения файлов из хранилища
 */
class FilesStorage extends AbstractHelper 
{
	protected $FilesLib;


public function __invoke($razdel,$razdel_id)
{
	$files=$this->FilesLib->loadFiles($razdel,$razdel_id);
    $view=$this->getView();
    $rez=[];
    foreach ($files as $k=>$f){
        $rez[$k]=$view->basePath($f);
    }
    
	return $rez;
}

public function __construct ($FilesLib)
	{
		$this->FilesLib=$FilesLib;
	}

}
