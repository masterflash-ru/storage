<?php

namespace Mf\Storage;

use Mf\Migrations\AbstractMigration;
use Mf\Migrations\MigrationInterface;
use Zend\Db\Sql\Ddl;

class Version20191104163546 extends AbstractMigration implements MigrationInterface
{
    public static $description = "Create table for Storage";

    public function up($schema, $adapter)
    {
        $table = new Ddl\CreateTable("storage");
        $table->addColumn(new Ddl\Column\Integer('id',false,null,["AUTO_INCREMENT"=>true]));
        $table->addColumn(new Ddl\Column\Char('razdel', 50,false,null,["COMMENT"=>"раздел, например, news"]));
        $table->addColumn(new Ddl\Column\Integer('todelete',true,0,["COMMENT"=>"флаг что нужно удалить эти фото"]));
        $table->addColumn(new Ddl\Column\Text('file_array',null,true,null,["COMMENT"=>"структура serialize массива имен файлов"]));
        $table->addColumn(new Ddl\Column\Floating('version',9,1,true,0,["COMMENT"=>"версия хранилища"]));
        $table->addConstraint(
            new Ddl\Constraint\PrimaryKey(['id','razdel'])
        );
        $table->addConstraint(
            new Ddl\Index\Index(['todelete'])
        );
        $this->addSql($table);
    }

    public function down($schema, $adapter)
    {
        $drop = new Ddl\DropTable('storage');
        $this->addSql($drop);
    }
}
