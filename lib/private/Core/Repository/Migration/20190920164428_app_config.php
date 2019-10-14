<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class AppConfig extends KeestashMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change() {

        $this->table(
            "app_config"
            , ["id" => false, "primary_key" => "app_id"]
        )
            ->addColumn(
                "app_id"
                , "string"
                , [
                    "null"      => false
                    , "comment" => "The App id"
                ]
            )
            ->addColumn(
                "enabled"
                , "enum"
                , [
                    "null"      => false
                    , "default" => "false"
                    , "values"  => [
                        "true"
                        , "false"
                    ]
                    , "comment" => "Whether the app is enabled or not"
                ]
            )
            ->addColumn(
                "version"
                , "integer"
                , [
                    "null"      => false
                    , "default" => 1
                    , "comment" => "The apps installed version"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->save();
    }

}
