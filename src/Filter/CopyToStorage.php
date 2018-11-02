<?php
/**
фильтр копирования в хранилище картинок
 */
namespace Mf\Storage\Filter;

use Zend\Filter\FilterInterface;
use Exception;


class CopyToStorage  implements FilterInterface
{


	protected $_options = [
        'target_folder' => '',          //базовый путь локальный к корню хранилища
        'folder_level' => 3,            //уровень вложений
        'folder_name_size' =>3,         //размер подпапок
        /*
        *стратегия переименования файлов:
        * none - оставить как есть
        * md5,sha1,uniqid - применение одноименных функций
        * translit - переводит в латиницу (поддерживается только кирилица!)
        */
        'strategy_new_name' => 'md5'
        
	];

public function __construct($options = array())
{
	$this->setOptions($options);
}
	

/**
собственно сам фильтр
на входе путь+ файл, главное что бы он читался, путь любой
*/
public function filter($value)
{

    $target=rtrim($this->_options['target_folder'],DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

    $newfilename=$this->CreateName($value);
    //смотрим есть ли каталоги в новом пути, если нет, создаем
	$path=dirname( $target.$newfilename);
	if (!is_dir($path)) {mkdir($path,0777,true);}


	if (!copy($value,$target.$newfilename)) {
            throw new Exception("Не удается скопировать файл из {$value}, в {$target}");
    }
    return $target.$newfilename;
}

/*

генерация имени файла исходя из исходного
по сути берется его расширение, и приклеивается к md5(microtime()) и все
$filename - имя файла на входе
стратегия переименования: уникальные хеши, транслит, и как есть, но имя чистится от пробелов и прочих запрещеных символов
возвращает строку включая путь вида fdg/sdf/sdfdsgdfgdfg.jpg
*/
protected function CreateName($filename)
{
    $pinfo=pathinfo($filename);
    switch (strtolower($this->_options['strategy_new_name'])) {
        case 'md5':{
            $name=md5(rand(0,10).microtime(true)).".".strtolower($pinfo["extension"]);
            break;
        }
        case 'uniqid':{
            $name=uniqid("_",true).".".strtolower($pinfo["extension"]);
            break;
        }
        case 'sha1':{
            $name=sha1(rand(0,10).microtime(true)).".".strtolower($pinfo["extension"]);
            break;
        }
        case 'translit':{
            $name=$this->translit($pinfo["filename"]).".".strtolower($pinfo["extension"]);
            break;
        }
            default:{
                $name=preg_replace("/[^0-9а-яА-Яa-zA-Z_\-.]/iu","",$pinfo['filename'].".".strtolower($pinfo["extension"]));
            }   
    }

	$p=[];
	for($i=0; $i<(int)$this->_options['folder_level']; $i++)
		{
			$p[]=substr($name,$i*$this->_options['folder_name_size'],$this->_options["folder_name_size"]);
		}
	$pp=implode(DIRECTORY_SEPARATOR,$p);

	return trim($pp.DIRECTORY_SEPARATOR.str_replace(implode("",$p),"",$name),DIRECTORY_SEPARATOR);
}

    
    
    
/*
установить опции
*/
public function setOptions(array $options)
	{
		if (!is_array($options)) {
			throw new Exception("Не допустимая опция, должен быть массив");	
		}
        
        if (!empty($options)&& is_array($options)){
            foreach ($options as $k => $v) {
                    if (array_key_exists($k, $this->_options)) {$this->_options[$k] = $v;}
            }
        }


		return $this;
	}

protected function translit($string)
{
    $string=preg_replace('/[^0-9a-zA-Z_а-яА-Я\-]/iu', '',$string);
	 $ru =implode('%', 
		array(
		'А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е', 'Ё', 'ё', 'Ж', 'ж', 'З', 'з',
		'И', 'и', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м', 'Н', 'н', 'О', 'о', 'П', 'п', 'Р', 'р',
		'С', 'с', 'Т', 'т', 'У', 'у', 'Ф', 'ф', 'Х', 'х', 'Ц', 'ц', 'Ч', 'ч', 'Ш', 'ш', 'Щ', 'щ',
		'Ъ', 'ъ', 'Ы', 'ы', 'Ь', 'ь', 'Э', 'э', 'Ю', 'ю', 'Я', 'я'
			)
			);
	$ru=explode ('%',mb_convert_encoding ($ru,'windows-1251','utf-8'));



	 $en = 
	 array(
		'A', 'a', 'B', 'b', 'V', 'v', 'G', 'g', 'D', 'd', 'E', 'e', 'E', 'e', 'Zh', 'zh', 'Z', 'z', 
		'I', 'i', 'J', 'j', 'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o', 'P', 'p', 'R', 'r',
		'S', 's', 'T', 't', 'U', 'u', 'F', 'f', 'H', 'h', 'C', 'c', 'Ch', 'ch', 'Sh', 'sh', 'Sch', 'sch',
		'_', '_', 'Y', 'y',  '', '', 'E', 'e', 'Ju', 'ju', 'Ja', 'ja'
	);
	
	$string=mb_convert_encoding ($string,'windows-1251','utf-8');
	
	
	$string = str_replace($ru, $en, $string);	
	return $string;
}

}
	

