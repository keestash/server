<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class ActivityDatabase extends KeestashMigration {

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
        $this->table(
            "activity"
            , [
                'id'            => false
                , 'primary_key' => [
                    'activity_id',
                ]
            ]
        )
            ->addColumn(
                "activity_id"
                , KeestashMigration::STRING
                , [
                    "null"      => false
                    , "comment" => "The activity id"
                ]
            )
            ->addColumn(
                "app_id"
                , KeestashMigration::STRING
                , [
                    "null"      => false
                    , "comment" => "The app id"
                ]
            )
            ->addColumn(
                "reference_key"
                , KeestashMigration::STRING
                , [
                    "null"      => false
                    , "comment" => "The key of the referencing record"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "default" => "CURRENT_TIMESTAMP"
                    , "comment" => 'the timestamp when ldap configuration was created'
                ]
            )
            ->addIndex(['app_id', 'reference_key'])
            ->save();

        $this->table("activity_data")
            ->addColumn(
                "description"
                , KeestashMigration::TEXT
                , [
                    "null"      => false
                    , "comment" => "The description"
                ]
            )
            ->addColumn(
                "activity_id"
                , KeestashMigration::STRING
                , [
                    "null"      => false
                    , "comment" => "The activity id (foreign key)"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addForeignKey(
                "activity_id"
                , "activity"
                , "activity_id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();
    }

}
