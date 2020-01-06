<?php
/**
*библиотека работы с файлами
*/

namespace Mf\Storage;

use Laminas\Router\Http\Literal;

return [

    'service_manager' => [
        'factories' => [//сервисы-фабрики
            Service\ImagesLib::class => Service\Factory\FilesLibFactory::class,
            Service\FilesLib::class => Service\Factory\FilesLibFactory::class,
        ],
        'aliases' => [
            "ImagesLib"=>Service\ImagesLib::class,
            "Imageslib"=>Service\ImagesLib::class,
            "imageslib"=>Service\ImagesLib::class,
            "FilesLib"=>Service\FilesLib::class,
            "Fileslib"=>Service\FilesLib::class,
            "fileslib"=>Service\FilesLib::class,
        ],
    ],

    'view_helpers' => [
        'factories' => [
            View\Helper\ImageStorage::class => View\Helper\Factory\ImageStorage::class,
            View\Helper\PictureStorage::class => View\Helper\Factory\ImageStorage::class,
            View\Helper\FilesStorage::class => View\Helper\Factory\FilesStorage::class,
        ],
        'aliases' => [
            'ImageStorage' => View\Helper\ImageStorage::class,
			'imagestorage' => View\Helper\ImageStorage::class,
            'FilesStorage' => View\Helper\FilesStorage::class,
			'filestorage' => View\Helper\FilesStorage::class,
            'PictureStorage' => View\Helper\PictureStorage::class,
			'picturestorage' => View\Helper\PictureStorage::class,
        ],
    ],
];
