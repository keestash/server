<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class RolesPermissions extends KeestashMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void {
        $this->table('role')
            ->addColumn(
                'name'
                , KeestashMigration::STRING
                , [
                    "comment"  => "The role's name"
                    , 'length' => '100'
                    , 'null'   => false
                    , 'after'  => 'id'
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The role's creation time as unix timestamp"
                    , "null"    => false
                    , "after"   => "name"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->save();

        $this->table('permission')
            ->addColumn(
                'name'
                , KeestashMigration::STRING
                , [
                    "comment"  => "The permissions's name"
                    , 'length' => '100'
                    , 'null'   => false
                    , 'after'  => 'id'
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The permissions's creation time as unix timestamp"
                    , "null"    => false
                    , "after"   => "name"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->save();

        $this->table('role_permission')
            ->addColumn(
                'role_id'
                , KeestashMigration::INTEGER
                , [
                    "comment" => "The role id"
                    , 'null'  => false
                    , 'after' => 'id'
                    , 'signed' => false
                ]
            )
            ->addColumn(
                "permission_id"
                , KeestashMigration::INTEGER
                , [
                    "comment" => "The permissions id"
                    , "null"  => false
                    , "after" => "role_id"
                    , 'signed'=> false
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The creation time as unix timestamp"
                    , "null"    => false
                    , "after"   => "name"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addForeignKey(
                "role_id"
                , "role"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->addForeignKey(
                "permission_id"
                , "permission"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();

        $this->table('role_user')
            ->addColumn(
                'role_id'
                , KeestashMigration::INTEGER
                , [
                    "comment" => "The role id"
                    , 'null'  => false
                    , 'after' => 'id'
                    , 'signed' =>false
                ]
            )
            ->addColumn(
                "user_id"
                , KeestashMigration::INTEGER
                , [
                    "comment" => "The user id"
                    , "null"  => false
                    , "after" => "role_id"
                    , 'signed' => false
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The creation time as unix timestamp"
                    , "null"    => false
                    , "after"   => "name"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addForeignKey(
                "role_id"
                , "role"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->addForeignKey(
                "user_id"
                , "user"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();

    }

}
