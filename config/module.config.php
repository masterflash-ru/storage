<?php
/**
библиотека работы с изображениями
 */

namespace Mf\Storage;


return [
    'service_manager' => [
        'factories' => [//сервисы-фабрики
            Service\ImagesLib::class => Service\Factory\FilesLibFactory::class,
            Service\FilesLib::class => Service\Factory\FilesLibFactory::class,
        ],
    ],

    'view_helpers' => [
        'factories' => [
            View\Helper\ImageStorage::class => View\Helper\Factory\ImageStorage::class,
            View\Helper\FilesStorage::class => View\Helper\Factory\FilesStorage::class,
        ],
        'aliases' => [
            'ImageStorage' => View\Helper\ImageStorage::class,
			'imagestorage' => View\Helper\ImageStorage::class,
            'FilesStorage' => View\Helper\FilesStorage::class,
			'filestorage' => View\Helper\FilesStorage::class,
			
        ],
    ],
    // Настройка кэша.
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

];
