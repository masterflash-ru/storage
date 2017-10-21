библиотека обработки изображений для Simba

После установки следует загрузить в базу дамп из папки data

Установка
composer require masterflash-ru/images

Библиотека предназначена для обработки графики, хранении ее в файловом хранилище. По требованию возвращается имя файла. В базе данных хранится только имя файла, причем для одного элемента может быть множество
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

        'items'=>[
            /*хранилище для ленты новостей, ключ это имя секции, которая используется для работы
            *он же является именем раздела, под которым записываются и считываются файлы*/
            
            "news"=>[
                "description"=>"Хранение фото новостей",
                'file_storage'=>'default', /*имя хранилища*/
                'images'=>[
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
use Images\Service\ImagesLib;
/*получить экземпляр*/
$imglib=$container->get(ImagesLib::class);
```
Запись в библиотеку:
```php
/* содержимое из конфига, обычно считывается из items по имени массива изображений*/
$arr=[
                "description"=>"Хранение фото новостей",
                'file_storage'=>'default',
                'images'=>[
                            'admin_img'=>[
                                'filters'=>[
                                        CopyToStorage::class => [
                                                    'folder_level'=>1,
                                                    'folder_name_size'=>3,
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
                                'validators' => [
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

    ];
$ImgLib->setMediaInfo($arr);
/*
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
Удаление:
```php
$ImgLib->deleteImage($razdel_name,$id);  //параметры аналогичны
```

Библиотека регистрирует помощник для view, при помощи которого можно получить сразу имя файла и путь, готовых для тега <img>
По сути помощник вызывает loadImage с этим же параметрами, дополнительно обрабатывает помощником basePath фреймворка
```html
<img src="<?=$this->ImageStorage($stream_name,$id,$item_name);?>" />
```