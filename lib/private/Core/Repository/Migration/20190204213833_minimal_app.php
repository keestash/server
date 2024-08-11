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

use Keestash\Core\DTO\User\UserStateName;
use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use Phinx\Migration\AbstractMigration;

class MinimalApp extends AbstractMigration {

    public function change() {
        $this->createUser();
        $this->createApiLog();
        $this->createToken();
    }

    private function createUser(): void {
        $this->table("user")
            ->addColumn(
                "name"
                , "string"
                , [
                    "length"    => "500"
                    , "comment" => "The user's name"
                    , "null"    => false
                    , "after"   => "id"
                ]
            )
            ->addColumn(
                "hash"
                , "text"
                , [
                    "comment" => "The user's hash"
                    , "null"  => false
                    , "after" => "name"
                ]
            )
            ->addColumn(
                "password"
                , "string"
                , [
                    "length"    => "500"
                    , "comment" => "The user's password"
                    , "null"    => false
                    , "after"   => "id"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "comment"   => "The user's creation time as unix timestamp"
                    , "null"    => false
                    , "after"   => "password"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addColumn(
                "first_name"
                , "string"
                , [
                    "null"    => false
                    , "after" => "id"
                ]
            )
            ->addColumn(
                "last_name"
                , "string"
                , [
                    "null"    => false
                    , "after" => "first_name"
                ]
            )
            ->addColumn(
                "email"
                , "string"
                , [
                    "null"    => false
                    , "after" => "name"
                ]
            )
            ->addColumn(
                "phone"
                , "string"
                , [
                    "null"    => false
                    , "after" => "email"
                ]
            )
            ->addColumn(
                "website"
                , "string"
                , [
                    "null"    => false
                    , "after" => "phone"
                ]
            )
            ->addColumn(
                "locale"
                , KeestashMigration::STRING
                , [
                    "length"    => "20"
                    , "null"    => false
                    , "default" => "US"
                    , "after"   => 'website'
                ]
            )
            ->addColumn(
                "language"
                , KeestashMigration::STRING
                , [
                    "length"    => "20"
                    , "null"    => false
                    , "default" => "en_US"
                    , "after"   => 'locale'
                ]
            )
            ->addIndex(
                ['name']
                , ['unique' => true]
            )
            ->addIndex(
                ['email']
                , ['unique' => true]
            )
            ->save();

        $this->createUserState();
    }

    private function createUserState(): void {
        $this->table("user_state")
            ->addColumn(
                "user_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "the user which's state is configured"
                    , 'signed'  => false
                ]
            )
            ->addColumn(
                "state"
                , KeestashMigration::ENUM
                , [
                    "null"                                  => false
                    , "comment"                             => "The user's state"
                    , KeestashMigration::OPTION_NAME_VALUES => [
                        UserStateName::DELETE->value,
                        UserStateName::LOCK->value,
                        UserStateName::REQUEST_PW_CHANGE->value,
                    ]
                    , "after"                               => "user_id"
                ]
            )
            ->addColumn(
                "valid_from"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "comment" => "The records valid from date"
                    , "after"   => "state"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "comment" => "The records create ts"
                    , "after"   => "valid_from"
                ]
            )
            ->addColumn(
                "state_hash"
                , KeestashMigration::STRING
                , [
                "null"      => true
                , "comment" => "The hash identifying the user state"
                , "after"   => "state"
                , "default" => null
            ])
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

    private function createApiLog(): void {

        $this->table("apilog")
            ->addColumn(
                "token_name"
                , "string"
                , [
                    "null"    => false
                    , "after" => "id"
                ]
            )
            ->addColumn(
                "token"
                , "string"
                , [
                    "null"    => false
                    , "after" => "token_name"
                ]
            )
            ->addColumn(
                "user_id"
                , "integer"
                , [
                    "null"     => false
                    , "after"  => "token"
                    , 'signed' => false
                ]
            )
            ->addColumn(
                "start_ts"
                , "decimal"
                , [
                    "null"        => false
                    , "precision" => 65
                    , "scale"     => 30
                    , "after"     => "user_id"
                ]
            )
            ->addColumn(
                "end_ts"
                , "decimal"
                , [
                    "null"        => false
                    , "precision" => 65
                    , "scale"     => 30
                    , "after"     => "start_ts"
                ]
            )
            ->addColumn(
                "route"
                , "string"
                , [
                    "null"    => false
                    , "after" => "end_ts"
                ]
            )
            ->addForeignKey(
                'user_id'
                , 'user'
                , [
                    'id'
                ]
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();
    }

    private function createToken(): void {
        $this->table("token")
            ->addColumn(
                "name"
                , "string"
                , [
                    "null"    => false
                    , "after" => "id"
                ]
            )
            ->addColumn(
                "value"
                , "string"
                , [
                    "null"    => false
                    , "after" => "name"
                ]
            )
            ->addColumn(
                "user_id"
                , "integer"
                , [
                    "null"     => false
                    , "after"  => "value"
                    , 'signed' => false
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "after"   => "user_id"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addForeignKey(
                'user_id'
                , 'user'
                , [
                    'id'
                ]
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();
    }


}
