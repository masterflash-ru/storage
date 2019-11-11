библиотека для работы с хранилищем файлов

После установки следует загрузить в базу дамп из папки data, или использовать пакет composer require masterflash-ru/migration, и загрузить дамп.

Установка
composer require masterflash-ru/storage

Библиотека предназначена для хранении файлов в файловом хранилище. По требованию возвращается имя файла. В базе данных хранится только имя файла, причем для одного элемента может быть множество
файлов с разными размерами.

В базу вместе с именами файлов записывается номер версии библиотеки, для будущих расширений, что бы в существующих хранилищах не производить изменения.

Регистрация в ZF3 производится стандартным образом, экземпляр библиотеки можно получить аналогично как и другие объекты, обычно в фабрике контроллера, или другой библиотеки-сервиса.

Конфигурация библиотеки описана ниже в примере, эту конфигурацию следует разместить в конфигурацию приложения:
```php
........
    /*хранилище и обработка (ресайз) фото*/
    "storage"=>[

        /*хранит загруженные файлы, готовые для обработки
        это промедуточное хранение
        */
        'data_folder'=>"data/images",

        /*
        *Именованные хранилища фото в виде множества вложенных папок
        *по умолчанию имеется всегда default
        *уровень вложений и размеры имен каталогов определяются параметром в фильтр CopyToStorage
        */
        'file_storage'=>[
            'default'=>[
                'base_url'=>"media/pics/",
            ],
        ],
        
        /*чистить хранилище только по расписанию, используется при очень больших хранилищах
        *когда за текущую операцию не успевает обойти все каталоги
        * включите по расписанию обращение к адресу http://site.ru/clear-storage-cron
        */
        "clear_storage_only_cron"=>false,
        'items'=>[
            /*хранилище для ленты новостей, ключ это имя секции, которая используется для работы
            *он же является именем раздела, под которым записываются и считываются файлы*/
            
            "news"=>[
                "description"=>"Хранение фото новостей",
                'file_storage'=>'default', /*имя хранилища*/
                'file_rules'=>[
                            'admin_img'=>[
                                'filters'=>[
                                        CopyToStorage::class => [   /*Наличе этого фильтра ОБЯЗАТЕЛЬНО!*/
                                                    'folder_level'=>1,
                                                    'folder_name_size'=>3,
                                                    'strategy_new_name'=>'translit' /*стратегия создания нового имени, none, md5, sha1, translit, uniqid*/
                                        ],
                                        ImgResize::class=>[
                                                    "method"=>1,
                                                    "width"=>150,
                                                    "height"=>150,
                                                    'adapter'=>Gd::class,
                                        ],
                                        ImgOptimize::class=>[
                                                    "jpegoptim"=>85,
                                                    "optipng"=>3,
                                        ],
                                        Watermark::class=>[
                                                    "waterimage"=>"data/images/water2.png",
                                                    'adapter'=>'Consoleimagick',
                                        ],
    
                                ],
                                'validators' => [/*валидаторы достаточно применить для одной ветки, т.к. последующие ветки используют исходное изображание вновь*/
                                        IsImage::class=>[],
                                        ImageSize::class => [
                                            'minWidth' => 500,
                                            'minHeight' => 250,
                                    ],
                                ],
                            ],
                            'anons'=>[
                                'filters'=>[
                                        CopyToStorage::class => [
                                                    'folder_level'=>1,
                                                    'folder_name_size'=>3,
                                        ],
                                        ImgResize::class=>[
                                                    "method"=>1,
                                                    "width"=>500,
                                                    "height"=>250,
                                                    'adapter'=>'gd',
                                        ],
                                ],
                            ],
                ],

            ],//news
        ],
    ],
.......
```

```php
/*объект ImagesLib регистрируется в менеджере, его можно извлечь в фабриках, если нужна обработка*/
use Images\Service\ImagesLib;

/*получить экземпляр*/
$imglib=$container->get(ImagesLib::class);
```
Запись в библиотеку:
```php
/*
вначале нужно выбрать секцию из конфига, в которой указаны правила обработки фото (куда писать, и как уменьшать)
$name - имя секции конфига с правилами обработки файлов, из примера выше это "news"
*/
$ImgLib->selectStorageItem($name);

/*
далее нужно передать исходный файл в виде пути и имени, имя раздела и идентификатор записи раздела
$filename - исходное имя файла, как правило в data/images,
$razdel - имя раздела, например, news (обычно совпадает с именем массива фото, и равно ключу в имени конфига),
$razdel_id - внутренний идентификатор элемента, например, ID новости в ленте
*/
$ImgLib->saveImages($filename,$razdel,$razdel_id);
```

Чтение:
```php
/*
$razdel_name - строка имени раздела, например, news
$id - ID новости, или какой-либо идентификатор под которым хранится изображение,
$item_name - имя самого изображения, например, anons
полностью готовый URL путь+имя файла для подставновки в тег <img>
*/
$img=$ImgLib->loadImage($razdel_name,$id,$item_name);

```
Удаление в разделе элемента:
```php
//$razdel_name - имя раздела
$id - идентификатор раздела, например, ID новости
$ImgLib->deleteFile($razdel_name,$id);
```

Удаление всех элементов во всем разделе:
```php
//$razdel_name - имя раздела
$ImgLib->deleteFileRazdel($razdel_name);
```

Библиотека регистрирует помощник для view, при помощи которого можно получить сразу имя файла и путь, готовых для тега <img>
По сути помощник вызывает loadImage с этим же параметрами, дополнительно обрабатывает помощником basePath фреймворка
```html
<img src="<?=$this->ImageStorage($stream_name,$id,$storage_item_name);?>" />
```
Имеется помощник PictureStorage, который генерирует новомодный тег <picture>, например,
```html
<?=$this->PictureStorage($stream_name,$id,$storage_item_name,["alt"=>"Подпись фото"]);?>
```
4-й параметр массив атрибут для тега img, вставляется как есть

В конфиге приложения должны быть настройки кэша:
```php

    'caches' => [
        'DefaultSystemCache' => [
            'adapter' => [
                'name'    => Filesystem::class,
                'options' => [
                    'cache_dir' => './data/cache',
                    'ttl' => 60*60*2 
                ],
            ],
            'plugins' => [
                [
                    'name' => Serializer::class,
                    'options' => [
                    ],
                ],
            ],
        ],
    ],
```
Для работы с базой в конфиге приложения должно быть объявлено DefaultSystemDb:
```php
......
    "databases"=>[
        //соединение с базой + имя драйвера
        'DefaultSystemDb' => [
            'driver'=>'MysqlPdo',
            //"unix_socket"=>"/tmp/mysql.sock",
            "host"=>"localhost",
            'login'=>"root",
            "password"=>"**********",
            "database"=>"simba4",
            "locale"=>"ru_RU",
            "character"=>"utf8"
        ],
    ],
.....
```
Если хранилище не большого размера, то можно чистку делать при каждой операции в нем, для этого в конфиге "clear_storage_only_cron" присвойте true. 
В принципе можно не чистить, если операций не много.
Для очистки хранилища можно использовать консольную команду из корня приложения:
```bash
./vendor/bin/storage clear all
 ```
 Данная команда требует установки пакета symfony/console, при помощи команды composer require symfony/console

