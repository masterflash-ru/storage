<?php
/**
фильтр копирования в хранилище файлов
 */
namespace Mf\Storage\Filter\Adapter;


use Exception;


class LocalFileSystem extends AbstractFileSystem
{

/*конструктор в абстрактном классе*/
	

/**
* непоссредственно копирует в локальной файловой системе
* на входе строка пути+имя файла исходного файла
* на выходе строка пути+имя файла конечного файла
* если в конечной точке нет каталогов, они создаются автоматически
*/
public function copy($from, $to)
{
    /*папка куда копируем*/
    $target=rtrim($this->_options['target_folder'],DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    //смотрим есть ли каталоги в новом пути, если нет, создаем
	$path=dirname( $target.$to);
	if (!is_dir($path)) {
        mkdir($path,0777,true);
    }

    if (!copy($from,$target.$to)) {
            throw new Exception("Не удается скопировать файл из {$from}, в {$target}");
    }
    return $target.$to;
}

}
	

