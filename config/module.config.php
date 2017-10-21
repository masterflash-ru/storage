<?php
/**
библиотека работы с изображениями
 */

namespace Storage;


return [
    'service_manager' => [
        'factories' => [//сервисы-фабрики
            Service\ImagesLib::class => Service\Factory\ImagesLibFactory::class,
        ],
    ],

    'view_helpers' => [
        'factories' => [
            View\Helper\ImageStorage::class => View\Helper\Factory\ImageStorage::class,
        ],
        'aliases' => [
            'ImageStorage' => View\Helper\ImageStorage::class,
			'imagestorage' => View\Helper\ImageStorage::class,
			
        ],
    ],
];
