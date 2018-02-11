<?php
namespace Mf\Storage\Service;

/*
сервис обработки фото и хранения
*/


class ImagesLib extends AbstractStorage
{



/*
Создание библиотеки фото по результатам информации, переданной в selectStorageItem
на входе имя файла, который находится во временном хранилище БЕЗ ПУТИ!
$filename = имя файла из которого создаются все размеры, обычно в data/images
$razdel - имя раздела, например, news
$razdel_id - уникальный номер записи, обычно ID записи, например в новостях

*/
public function saveImages($filename,$razdel,$razdel_id)
{
	return $this->saveFiles($filename,$razdel,$razdel_id);
}





/*
получить путь к файлу
$razdel - имя раздела, например, news,
$razdel_id - ID элемента, например ID новости,
$item_name - имя фотоэлемента, например, admin_img или anons
возвращает полный URL путь с файлом, готовые для вставки в тег <img....
*/
public function loadImage($razdel,$razdel_id,$item_name)
{
    return $this->loadFile($razdel,$razdel_id,$item_name);
}




}
