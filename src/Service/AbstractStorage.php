<?php
namespace Mf\Storage\Service;

use Exception;
use ADO\Service\RecordSet;
use Zend\Validator\ValidatorChain;
use Zend\Filter\FilterChain;

/*
сервис  хранения
*/


abstract class AbstractStorage 
{
    const VERSION=1;
	protected $config;
	protected $connection;
	protected $base_public_path;
	protected $source_folder;
	protected $cache;
	protected $media;
    
/*
* $connection - ADO коннект к базе
*$config - значение конфига из секции "storage"
*$cache - объект кеширования
*/
public function __construct($connection,$config,$cache) 
{
	$this->config=$config;
	$this->connection=$connection;
	$this->cache=$cache;
	$this->source_folder=rtrim(getcwd().DIRECTORY_SEPARATOR.$config['data_folder'],DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
}


/*
активирует из списка элементов обработчиков хранилища нужный элемент, 
передается ключ к массиву для items
*/
public function selectStorageItem($name)
{
    if (empty($this->config['items'][$name])){
        throw new Exception("Элемента $name нет в настройках элементов хранилища");
    }
	$this->media=$this->config['items'][$name];
    $this->base_public_path=rtrim(
            getcwd().
            str_replace(getcwd(),"",$_SERVER['DOCUMENT_ROOT']).
            DIRECTORY_SEPARATOR .
            $this->config['file_storage'][$this->media["file_storage"]] ["base_url"],DIRECTORY_SEPARATOR
            ).DIRECTORY_SEPARATOR;
}


/*
Создание библиотеки файлов по результатам информации, переданной в selectStorageItem
на входе имя файла, который находится во временном хранилище БЕЗ ПУТИ!
$filename = имя файла из которого создаются все размеры, обычно в data/images
$razdel - имя раздела, например, news
$razdel_id - уникальный номер записи, обычно ID записи, например в новостях
*/
public function saveFiles($filename,$razdel,$razdel_id)
{
	$rez=[];
	$size_info=$this->media['file_rules'];
	foreach ($size_info as $size_name=>$size_info){
        /*смотрим валидаторы и применяем ДО обработки фильтрами*/
        if (!empty($size_info["validators"]) && is_array($size_info["validators"])){
            //чтение фильтров и применение их
            $vChain = new ValidatorChain();
            foreach ($size_info["validators"] as $validator=>$options){
                $vChain->attachByName($validator,$options);
            }
                
            if (!$vChain->isValid($this->source_folder.$filename)){
                foreach ($vChain->getMessages() as $message) {
                    echo "<b>$message\n</b><br>";
                }
                return;
            }
            
        }
        $rez[$size_name]='';

        $filterChain = new FilterChain();

		if (!empty($size_info["filters"]) && is_array($size_info["filters"])){
            //чтение фильтров и применение их
            
            foreach ($size_info["filters"] as $filter=>$options){
                $options['target_folder']=$this->base_public_path;
                $filterChain->attachByName($filter,$options);
            }
                
            $new=$filterChain->filter($this->source_folder.$filename);
            //удалим базовый путь, что бы выделить разбитое имя
            $rez[$size_name]=str_replace($this->base_public_path,'',$new);
        }
    }
        
    $rez['file_storage']=$this->media['file_storage'];

	//запишем в базу результат
	$rs=new RecordSet();
	$rs->CursorType = adOpenKeyset;
	$rs->open("SELECT * FROM storage where id=".$razdel_id." and razdel='".$razdel."'",$this->connection);
	if ($rs->EOF){
		//новая
        $rs->AddNew();
        $rs->Fields->Item["razdel"]->Value=$razdel;
        $rs->Fields->Item["id"]->Value=$razdel_id;
        $rs->Fields->Item["todelete"]->Value=0;
        $rs->Fields->Item["version"]->Value=self::VERSION;
    } else  {
        //удалим старые файлы
        $del=unserialize($rs->Fields->Item["file_array"]->Value);
        $this->delItem($del);
        //чистим кеш
        $razdel=preg_replace('/[^0-9a-zA-Z_\-]/iu', '',$razdel);
        $this->cache->removeItem("storage_lib_{$razdel}_".$razdel_id);
	}
    $rs->Fields->Item["file_array"]->Value=serialize($rez);
    $rs->Update();
    //удалим исходный фал
    unlink($this->source_folder.$filename);
    return $rez;
}


/*
получить путь к файлу+ сам файл
$razdel - имя раздела, например, news,
$razdel_id - ID элемента, например ID новости,
$item_name - имя фотоэлемента, например, admin_img или anons
возвращает полный URL путь с файлом, готовые для вставки в тег <img....
*/
public function loadFile($razdel,$razdel_id,$item_name)
{
    $rez=$this->loadFilesArray($razdel,$razdel_id);
    /*получить само хранилище по имени*/
    if (!empty($rez['file_storage'])) {
            $file_storage_name=$rez['file_storage'];
        } else {
            $file_storage_name="default";
        }
        
    $base_url=$this->config['file_storage'][$file_storage_name]['base_url'];
	if (isset($rez[$item_name])) {return $base_url.$rez[$item_name];}
	return "";
}

/*
получить путь к файлам+ сами файлы - МАССИВ! 
$razdel - имя раздела, например, news,
$razdel_id - ID элемента, например ID новости,
$item_name - имя фотоэлемента, например, admin_img или anons
возвращает полный URL путь с файлом, готовые для вставки в тег <img....
*/
public function loadFiles($razdel,$razdel_id)
{
    $rez=$this->loadFilesArray($razdel,$razdel_id);
    /*получить само хранилище по имени*/
    if (!empty($rez['file_storage'])) {
            $file_storage_name=$rez['file_storage'];
        } else {
            $file_storage_name="default";
        }
    /*получить само хранилище по имени*/
        if (!empty($rez['file_storage'])) {
            $file_storage_name=$rez['file_storage'];
        } else {
            $file_storage_name="default";
        }
    $base_url=$this->config['file_storage'][$file_storage_name]['base_url'];
    unset($rez['file_storage']);
    $ret=[];
    foreach ($rez as $i){
        $ret[]=$base_url.$i;
    }
    return $ret;
}

    
/*
получить путь к файлу+ сам файл для всех элементов в виде массива
$razdel - имя раздела, например, news,
$razdel_id - ID элемента, например ID новости,
$item_name - имя фотоэлемента, например, admin_img или anons
возвращает полный URL путь с файлом, готовые для вставки в тег <img....
*/
public function loadFilesArray($razdel,$razdel_id)
{
    $razdel_id=(int)$razdel_id;
	 $result = false;
	 $key="storage_lib_".preg_replace('/[^0-9a-zA-Z_\-]/iu', '',$razdel)."_{$razdel_id}";

     $rez = $this->cache->getItem($key, $result);
     if (!$result){
         $rez=[];
         $rs=new RecordSet();
         $rs->open("SELECT * FROM storage where id=".$razdel_id." and razdel='{$razdel}'",$this->connection);
         if (!$rs->EOF){
             $rez=unserialize($rs->Fields->Item["file_array"]->Value);
             if (!empty($rez)) {$this->cache->setItem($key, $rez);}
         }
     }
    return $rez;    
}


/*
удалить массив файлов
$razdel - имя раздела, например, news,
$razdel_id - ID элемента, например ID новости,
*/
public function deleteFile($razdel,$razdel_id)
{
	$razdel_id=(int)$razdel_id;
	$rs=new RecordSet();
	$rs->CursorType = adOpenKeyset;
	$rs->open("SELECT * FROM storage where id=".$razdel_id." and razdel='{$razdel}' or todelete>0",$this->connection);
	while(!$rs->EOF){
        $del=unserialize($rs->Fields->Item["file_array"]->Value);
        $this->delItem($del);
        $rs->Delete();
        $rs->Update();
        $rs->MoveNext();
    }
	$this->deleteEmptyDir();
	$razdel=preg_replace('/[^0-9a-zA-Z_\-]/iu', '',$razdel);
	$this->cache->removeItem("storage_lib_{$razdel}_{$razdel_id}");
}


/*
обход каталогов хранилища и удаление пустых каталогов
*/
protected function deleteEmptyDir()
{
    foreach ($this->config['file_storage'] as $storage_item) {
        $base_public_path=rtrim(
            getcwd().
            str_replace(getcwd(),"",$_SERVER['DOCUMENT_ROOT']).
            DIRECTORY_SEPARATOR .
            $storage_item ["base_url"],DIRECTORY_SEPARATOR
            );
        try {
            $idir = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $base_public_path, \FilesystemIterator::SKIP_DOTS ), \RecursiveIteratorIterator::CHILD_FIRST );
        }
        catch (\UnexpectedValueException $e) { return;}
        
        foreach( $idir as $v ){
            if( $v->isDir() and $v->isWritable() ){
                $f = glob( $idir->key() . '/*.*' );
                if( empty( $f ) ){
                    @rmdir( $idir->key() );
                }
            }
        } 
    }
}

/*
вспомогательная удаление файлов согласно описания 
*/
protected function delItem($del)
{
/*получить само хранилище по имени*/
    if (!empty($del['file_storage'])) {
        $file_storage_name=$del['file_storage'];
    } else {
        $file_storage_name="default";
    }
    $base_url=rtrim(
                        getcwd().
                        str_replace(getcwd(),"",$_SERVER['DOCUMENT_ROOT']).
                        DIRECTORY_SEPARATOR .
                        $this->config['file_storage'][$file_storage_name]['base_url'],DIRECTORY_SEPARATOR
                        ).DIRECTORY_SEPARATOR;
    unset($del['file_storage']);
    unset($del['version']);
    foreach ($del as $item){
        unlink ($base_url.$item);
    }
}

}
