<?php

namespace Mf\Storage;

use Mf\Migrations\AbstractMigration;
use Mf\Migrations\MigrationInterface;

class Version20191104163546 extends AbstractMigration implements MigrationInterface
{
    public static $description = "Create table for Storage";

    public function up($schema)
    {
        switch ($this->db_type){
            case "mysql":{
                $this->addSql("CREATE TABLE `storage` (
                  `id` int(11) unsigned NOT NULL,
                  `razdel` char(50) NOT NULL COMMENT 'раздел, например, news',
                  `todelete` int(11) DEFAULT '0' COMMENT 'флаг что нужно удалить эти фото',
                  `file_array` text COMMENT 'структура serialize массива имен файлов',
                  `version` float(9,1) DEFAULT NULL COMMENT 'версия хранилища',
                  PRIMARY KEY (`id`,`razdel`),
                  KEY `todelete` (`todelete`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Хранилище файлов'");
                break;
            }
            default:{
                throw new \Exception("the database {$this->db_type} is not supported !");
            }
        }
    }

    public function down($schema)
    {
        switch ($this->db_type){
            case "mysql":{
                $this->addSql("DROP TABLE `storage`");
                break;
            }
            default:{
                throw new \Exception("the database {$this->db_type} is not supported !");
            }
        }
    }
}
