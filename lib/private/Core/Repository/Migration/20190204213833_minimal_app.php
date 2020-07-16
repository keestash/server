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
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Permission\IPermission;
use KSP\Core\Permission\IRole;
use Phinx\Migration\AbstractMigration;

class MinimalApp extends AbstractMigration {

    public function change() {
        $this->createUser();
        $this->createPermissionManagement();
        $this->insertDefaultPermissions();
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
                    , "comment" => "The user's name"
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
                ]
            )
            ->addColumn(
                "state"
                , KeestashMigration::ENUM
                , [
                    "null"                                  => false
                    , "comment"                             => "The user's state"
                    , KeestashMigration::OPTION_NAME_VALUES => [
                        IUserState::USER_STATE_DELETE
                        , IUserState::USER_STATE_LOCK
                        , IUserState::USER_STATE_REQUEST_PW_CHANGE
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

    private function createPermission(): void {
        $this->table("permission")
            ->addColumn(
                "name"
                , "string"
                , [
                    "null"      => "false"
                    , "comment" => "The permissions name"
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
            ->addIndex(
                "id"
                , [
                    "unique" => true
                ]
            )
            ->addIndex(
                "name"
                , [
                    "unique" => true
                ]
            )
            ->save();

    }


    private function createRole(): void {
        $this->table("role")
            ->addColumn(
                "name"
                , "string"
                , [
                    "null"    => false
                    , "after" => "id"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "after"   => "name"
                    , "default" => "CURRENT_TIMESTAMP"

                ]
            )
            ->addIndex(
                "id"
                , [
                    "unique" => true
                ]
            )
            ->addIndex(
                "name"
                , [
                    "unique" => true
                ]
            )
            ->save();

    }


    private function createRolePermission(): void {
        $this->table("permission_role")
            ->addColumn(
                "permission_id"
                , "integer"
                , [
                    "null"    => false
                    , "after" => "id"
                ]
            )
            ->addColumn(
                "role_id"
                , "integer"
                , [
                    "null"    => false
                    , "after" => "permission_id"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "after"   => "name"
                    , "default" => "CURRENT_TIMESTAMP"

                ]
            )
            ->addIndex(
                "id"
                , [
                    "unique" => true
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
            ->addForeignKey(
                "role_id"
                , "role"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();

    }

    private function createUserRole(): void {

        $this->table("user_role")
            ->addColumn(
                "role_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "The user's role"
                ]
            )
            ->addColumn(
                "user_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "The user"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "comment" => "The record's create ts"
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


    private function createPermissionManagement(): void {
        $this->createPermission();
        $this->createRole();
        $this->createRolePermission();
        $this->createUserRole();
    }

    private function insertDefaultPermissions(): void {
        $this->table("permission")
            ->insert(
                [
                    "id"     => IPermission::ID_PUBLIC
                    , "name" => "Public Permission"
                ]
            )
            ->save();

        $this->table("role")
            ->insert(
                [
                    "id"     => IRole::ID_PUBLIC
                    , "name" => "Public Role"
                ]
            )
            ->insert(
                [
                    "id"     => IRole::ID_ADMIN
                    , "name" => "Admin Role"
                ]
            )
            ->insert(
                [
                    "id"     => IRole::ID_APP_USER
                    , "name" => "Regular logged in App User Role"
                ]
            )
            ->insert(
                [
                    "id"     => IRole::ID_SYSTEM
                    , "name" => "System User, for System related tasks"
                ]
            )
            ->save();

        $this->table("permission_role")
            ->insert(
                [
                    "permission_id" => IPermission::ID_PUBLIC
                    , "role_id"     => IRole::ID_PUBLIC
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
                    "null"    => false
                    , "after" => "token"
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
                    "null"    => false
                    , "after" => "value"
                ]
            )
            ->addColumn(
                "create_ts"
                , "integer"
                , [
                    "null"    => false
                    , "after" => "user_id"
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
