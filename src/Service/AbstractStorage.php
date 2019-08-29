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
    const VERSION=2;
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
        throw new Exception("Элемента '$name' нет в настройках элементов хранилища");
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
    $razdel_id=(int)$razdel_id;
	$rez=[];
	$size_info=$this->media['file_rules'];
    /*цикл по параметрам секции file_rules выбранного раздела из storage*/
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
                    trigger_error($message, E_USER_WARNING);
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
            foreach ($new as $k=>$item){
                $new[$k]=str_replace($this->base_public_path,'',$new[$k]);
            }
            $rez[$size_name]=$new;
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
    } else  {
        //удалим старые файлы
        $del=unserialize($rs->Fields->Item["file_array"]->Value);
        $this->delItem($del,$rs->Fields->Item["version"]->Value);
        //чистим кеш
        $razdel=preg_replace('/[^0-9a-zA-Z_\-]/iu', '',$razdel);
        $this->cache->removeItem("storage_lib_{$razdel}_".$razdel_id);
	}
    $rs->Fields->Item["version"]->Value=static::VERSION;
    $rs->Fields->Item["file_array"]->Value=serialize($rez);
    $rs->Update();
    //удалим исходный фал
    unlink($this->source_folder.$filename);
    return $rez;
}
/**
* проверить сущетсвование файлов в хранилище по имени раздела и ID
* $razdel - имя раздела, например, news,
* $razdel_id - ID элемента, например ID новости,
*/
public function hasFile($razdel,$razdel_id)
{
    $razdel_id=(int)$razdel_id;
    $rs=$this->connection->Execute("SELECT id FROM storage where id=".$razdel_id." and razdel='{$razdel}' limit 1");
    return !$rs->EOF;
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
    /**
    * возвращаем данные для версии 1
    */
    $base_url=$this->config['file_storage'][$file_storage_name]['base_url'];
    
    if (isset($rez[$item_name]) && $rez["version"]==1) {
        return $base_url.$rez[$item_name];
    }
    if (isset($rez[$item_name]["default"])&& $rez["version"]==2) {
        return $base_url.$rez[$item_name]["default"];
    }
    
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
    if (empty($rez["version"])){
        return [];
    } 
    $version=$rez["version"]; //версия записи
    $base_url=$this->config['file_storage'][$file_storage_name]['base_url'];
    unset($rez['file_storage']);
    unset($rez["version"]);
    $ret=[];
    switch ($version){
        case 1:{
            foreach ($rez as $k=>$i){
                $ret[$k]=$base_url.$i;
            }
            break;
        }
        case 2:{
            foreach ($rez as $item){
                foreach ($item as $k=>$i){
                    $ret[$k]=$base_url.$i;
                }
            }
            break;
        }
    }
    return $ret;
}

/**
* перемещение файла в другое хранилище
* $old_name - старое имя хранилище (имя секции из конфига)
* $new_name - новое имя хранилища из конфига
* $id - идентификатор файла
* ничего не возвращает, если базовые пути отличаются, тогда исключение, этот тип переноса пока не работает
*/
public function renameImages(string $old_name,string $new_name,$id)
{
    $id=(int)$id;
    if (empty($this->config['items'][$old_name])){
        throw new Exception("Элемента '$old_name' нет в настройках элементов хранилища");
    }
    //старый путь к файлу
    $old_base_public_path=rtrim(
            getcwd().
            str_replace(getcwd(),"",$_SERVER['DOCUMENT_ROOT']).
            DIRECTORY_SEPARATOR .
            $this->config['file_storage'][$this->config['items'][$old_name]["file_storage"]] ["base_url"],DIRECTORY_SEPARATOR
            ).DIRECTORY_SEPARATOR;
    
    if (empty($this->config['items'][$new_name])){
        throw new Exception("Элемента '$new_name' нет в настройках элементов хранилища");
    }
    //новый путь
    $new_base_public_path=rtrim(
            getcwd().
            str_replace(getcwd(),"",$_SERVER['DOCUMENT_ROOT']).
            DIRECTORY_SEPARATOR .
            $this->config['file_storage'][$this->config['items'][$new_name]["file_storage"]] ["base_url"],DIRECTORY_SEPARATOR
            ).DIRECTORY_SEPARATOR;
    //если не совпадают, тогда переносим файл физически
    if ($old_base_public_path!=$new_base_public_path){
         throw new Exception("Пока не поддерживается перенос файлов в хранилище, расположенное в другом месте!");
    }
    //меняем запись в базе
    $this->clearStorage();
    $r=0;
    $this->connection->Execute("update storage set razdel='{$new_name}' where razdel='{$old_name}' and id=$id",$r,adExecuteNoRecords);
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
             $rez["version"]=(float)$rs->Fields->Item["version"]->Value;
             $this->cache->setItem($key, $rez);
             $this->cache->setTags($key,[$razdel]);
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
    $r=0;
    $this->connection->Execute("update storage set todelete=1 where id=".$razdel_id." and razdel='{$razdel}'",$r,adExecuteNoRecords);
    $this->clearStorage();
}

/*
удалить массив файлов всего раздела
$razdel - имя раздела, например, news,
*/
public function deleteFileRazdel($razdel)
{
    $r=0;
    $this->connection->Execute("update storage set todelete=1 where razdel='{$razdel}'",$r,adExecuteNoRecords);
    $this->clearStorage();
}

/**
* чистка хранилища
*/
public function clearStorage()
{
	$rs=new RecordSet();
    $rs->CursorType = adOpenKeyset;
	$rs->open("SELECT * FROM storage where  todelete>0",$this->connection);
    $razdel=[];
	while(!$rs->EOF){
        $del=unserialize($rs->Fields->Item["file_array"]->Value);
        $razdel[]=$rs->Fields->Item["razdel"]->Value;
        $this->delItem($del,$rs->Fields->Item["version"]->Value);
        $rs->Delete();
        $rs->Update();
        $rs->MoveNext();
    }
	$this->deleteEmptyDir();
    $this->cache->clearByTags($razdel,true);
    $rs->Close();
    $this->connection->Execute("delete from storage where todelete>0",$r,adExecuteNoRecords);
}


/*
*сервисные, получить путь к исходным файлам
*/
public function getSourceFolder()
{
    return $this->source_folder;
}

/*
* сервисные, установить папку в которой исходыне файлы
*/
public function setSourceFolder($path)
{
    $this->source_folder=$path;
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
protected function delItem($del,$version=1)
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
    //удаление для формата версии 1
    switch ($version){
        case 1:{
            foreach ($del as $item){
                unlink ($base_url.$item);
            }
            break;
        }
        case 2:{
            foreach ($del as $item){
                foreach ($item as $i){
                    unlink ($base_url.$i);
                }
            }
            break;
        }
    }
}

}
