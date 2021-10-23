<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\PasswordManager\Api\Comment\Add;
use KSA\PasswordManager\Api\Comment\Get;
use KSA\PasswordManager\Api\Comment\Remove;
use KSA\PasswordManager\Api\Generate\Generate;
use KSA\PasswordManager\Api\Import\Import;
use KSA\PasswordManager\Api\Node\Avatar\Update;
use KSA\PasswordManager\Api\Node\Credential\Create;
use KSA\PasswordManager\Api\Node\Delete;
use KSA\PasswordManager\Api\Node\GetByName;
use KSA\PasswordManager\Api\Node\Move;
use KSA\PasswordManager\Api\Node\ShareableUsers;
use KSA\PasswordManager\Api\Share\PublicShare;
use KSA\PasswordManager\Api\Share\PublicShareSingle;
use KSA\PasswordManager\Api\Share\Share;
use KSA\PasswordManager\ConfigProvider;
use KSP\Core\DTO\Http\IVerb;

return [
    CoreConfigProvider::ROUTES        => [
        [
            'path'         => '/password_manager/comment/add[/]'
            , 'middleware' => Add::class
            , 'method'     => IVerb::POST
            , 'name'       => Add::class
        ],
        [
            'path'         => '/password_manager/comment/get/:nodeId[/]'
            , 'middleware' => Get::class
            , 'method'     => IVerb::GET
            , 'name'       => Get::class
        ],
        [
            'path'         => '/password_manager/comment/remove[/]'
            , 'middleware' => Remove::class
            , 'method'     => IVerb::POST
            , 'name'       => Remove::class
        ],
        [
            'path'         => '/password_manager/generate_password/:length/:upperCase/:lowerCase/:digit/:specialChars[/]'
            , 'middleware' => Generate::class
            , 'method'     => IVerb::GET
            , 'name'       => Generate::class
        ],
        [
            'path'         => '/password_manager/import[/]'
            , 'middleware' => Import::class
            , 'method'     => IVerb::POST
            , 'name'       => Import::class
        ],
        [
            'path'         => '/password_manager/share/public[/]'
            , 'middleware' => PublicShare::class
            , 'method'     => IVerb::POST
            , 'name'       => PublicShare::class
        ],
        [
            'path'         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT
            , 'middleware' => PublicShareSingle::class
            , 'method'     => IVerb::GET
            , 'name'       => PublicShareSingle::class
        ],
        [
            'path'         => '/password_manager/share/remove[/]'
            , 'middleware' => \KSA\PasswordManager\Api\Share\Remove::class
            , 'method'     => IVerb::POST
            , 'name'       => \KSA\PasswordManager\Api\Share\Remove::class
        ],
        [
            'path'         => '/password_manager/share[/]'
            , 'middleware' => Share::class
            , 'method'     => IVerb::POST
            , 'name'       => Share::class
        ],
        [
            'path'         => '/password_manager/node/delete[/]'
            , 'middleware' => Delete::class
            , 'method'     => IVerb::POST
            , 'name'       => Delete::class
        ],
        [
            'path'         => '/password_manager/node/get/:id[/]'
            , 'middleware' => \KSA\PasswordManager\Api\Node\Get::class
            , 'method'     => IVerb::GET
            , 'name'       => \KSA\PasswordManager\Api\Node\Get::class
        ],
        [
            'path'         => '/password_manager/node/name/:name[/]'
            , 'middleware' => GetByName::class
            , 'method'     => IVerb::GET
            , 'name'       => GetByName::class
        ],
        [
            'path'         => '/password_manager/node/move[/]'
            , 'middleware' => Move::class
            , 'method'     => IVerb::POST
            , 'name'       => Move::class
        ],
        [
            'path'         => ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_ADD
            , 'middleware' => \KSA\PasswordManager\Api\Node\Organization\Add::class
            , 'method'     => IVerb::PUT
            , 'name'       => \KSA\PasswordManager\Api\Node\Organization\Add::class
        ],
        [
            'path'         => ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_UPDATE
            , 'middleware' => \KSA\PasswordManager\Api\Node\Organization\Update::class
            , 'method'     => IVerb::POST
            , 'name'       => \KSA\PasswordManager\Api\Node\Organization\Update::class
        ],
        [
            'path'         => '/password_manager/users/shareable/:nodeId/:query/'
            , 'middleware' => ShareableUsers::class
            , 'method'     => IVerb::GET
            , 'name'       => ShareableUsers::class
        ],
        [
            'path'         => '/password_manager/attachments/add[/]'
            , 'middleware' => \KSA\PasswordManager\Api\Node\Attachment\Add::class
            , 'method'     => IVerb::POST
            , 'name'       => \KSA\PasswordManager\Api\Node\Attachment\Add::class
        ],
        [
            'path'         => '/password_manager/attachments/get/:nodeId[/]'
            , 'middleware' => \KSA\PasswordManager\Api\Node\Attachment\Get::class
            , 'method'     => IVerb::GET
            , 'name'       => \KSA\PasswordManager\Api\Node\Attachment\Get::class
        ],
        [
            'path'         => '/password_manager/attachments/remove[/]'
            , 'middleware' => \KSA\PasswordManager\Api\Node\Attachment\Remove::class
            , 'method'     => IVerb::POST
            , 'name'       => \KSA\PasswordManager\Api\Node\Attachment\Remove::class
        ],
        [
            'path'         => '/password_manager/node/update/avatar[/]'
            , 'middleware' => Update::class
            , 'method'     => IVerb::POST
            , 'name'       => Update::class
        ],
        [
            'path'         => '/password_manager/node/credential/create[/]'
            , 'middleware' => Create::class
            , 'method'     => IVerb::POST
            , 'name'       => Create::class
        ],
        [
            'path'         => '/password_manager/credential/get/:id[/]'
            , 'middleware' => \KSA\PasswordManager\Api\Node\Credential\Password\Get::class
            , 'method'     => IVerb::GET
            , 'name'       => \KSA\PasswordManager\Api\Node\Credential\Password\Get::class
        ],
        [
            'path'         => '/password_manager/users/update[/]'
            , 'middleware' => \KSA\PasswordManager\Api\Node\Credential\Update::class
            , 'method'     => IVerb::POST
            , 'name'       => \KSA\PasswordManager\Api\Node\Credential\Update::class
        ],
        [
            'path'         => '/password_manager/node/create[/]'
            , 'middleware' => \KSA\PasswordManager\Api\Node\Folder\Create::class
            , 'method'     => IVerb::POST
            , 'name'       => \KSA\PasswordManager\Api\Node\Folder\Create::class
        ],
        [
            'path'         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_PASSWORD_UPDATE
            , 'middleware' => \KSA\PasswordManager\Api\Node\Credential\Password\Update::class
            , 'method'     => IVerb::POST
            , 'name'       => \KSA\PasswordManager\Api\Node\Credential\Password\Update::class
        ],
    ],
    CoreConfigProvider::PUBLIC_ROUTES => [
        ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT
    ]
];