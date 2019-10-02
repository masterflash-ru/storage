# API хранилища файлов

Используется пространство имен Mf\Storage.


Сервис Mf\Storage\ImagesLib он расширяет абстрактный класс AbstractStorage в котором все и производится. Сервис Mf\Storage\FilesLib полностью повторяет абстрактный класс.
Псевдонимы:
loadImage -> loadFile
hasImage -> hasFile
loadPictures -> loadFile


Вызовы абстрактного класса:

Вызов | описание
------|--------------
 selectStorageItem(string $name):void | Выбор имени элемента из хранилища (ключ из массима items конфига) 
 saveFiles(string $filename,string $razdel,int $razdel_id):array | Запись файла по правилам из выбранного элмента хранилища (см.выше), возавращает массив того что пишет в таблицу хранилища.
 hasFile($razdel,$razdel_id):boolean | Проверяет наличие записи и файла в хранилище по имени раздела и идентификатору раздела, например, новости
 loadFile($razdel,$razdel_id,$item_name):string | возвращает строку с путем и именем файла, пригодную для тега img
 loadFiles($razdel,$razdel_id):array | Аналогично loadFile, но возвращает массив
 renameImages(string $old_name,string $new_name,$id):void | Изменение имени хранилища (ключей из items), файл реально не меняется, поэтому перенос физически не поддерживается
 loadFilesArray($razdel,$razdel_id):array | Больше технологическая, возвращает массив того что хранится в записи хранилища, сильно зависит о версии хранения
 deleteFile($razdel,$razdel_id):void | Удаление файлов, функция ставит только метки, физически файлы не удаляются, если в конфиге clear_storage_only_cron - false, иначе удаляется
 deleteFileRazdel($razdel):void | Аналогично, но удалиь весь раздел
 clearStorage():void | Чистка хранилища, удаляет помеченные для удаления файлы, удалит пустые папки в файловой системе, если хранилища большое, тогда этот процесс будет длительным! используйте чистку по расписанию
 getSourceFolder():string | Получить папку с исходными файлами (по сути из конфига)
 setSourceFolder(string $path):void | Установить папку с исходными файлами для обработки
 
 
 во всех параметрах и в помощниках так же:
 1. $razdel - Имя раздела, совпадает с ключем items
 2. $razdel_id - идентификатор раздела, например, идентификатор новости
 3. $filename - имя файла которое находится во временной папке после загрузки, готовое к обработке
 4. $default_image - URL к изображению, если в хранилище ничего не найдено
 
 
Помощник Mf\Storage\View\Helper\ImageStorage

Применение (параметры см.выше):
```html
<img src="<?=$this->ImageStorage($razdel,$razdel_id,$item_name,$default_image)?>" alt="">
```

Помощник Mf\Storage\View\Helper\PictureStorage

Применение (параметры см.выше):
```php
/*
* $options - пока не используется
* если на входе все пусто, возвращается сам объект помощника
* на выходе новый тег <picture> 
*/
echo $this->PictureStorage($razdel=null,$razdel_id=null,$item_storage_name=null,array $options=[]);
```

Вызов | описание
------|--------------
__invoke($razdel=null,$razdel_id=null,$item_storage_name=null,array $options=[]) | Магический метод при обращении из сценария вывода. Если пусто возвратит сам объект, иначе строку HTML с тегом picture
setOptions(array $options=[]) | Установить опции 
render($razdel,$razdel_id,$item_storage_name) | По сути __invoke пока




