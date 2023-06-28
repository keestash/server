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
use Keestash\Middleware\DeactivatedRouteMiddleware;
use KSA\PasswordManager\Api\Node\Avatar\Update;
use KSA\PasswordManager\Api\Node\Credential\AdditionalData\GetValue;
use KSA\PasswordManager\Api\Node\Credential\Comment\Add;
use KSA\PasswordManager\Api\Node\Credential\Comment\Get;
use KSA\PasswordManager\Api\Node\Credential\Comment\Remove;
use KSA\PasswordManager\Api\Node\Credential\Create;
use KSA\PasswordManager\Api\Node\Credential\Generate\Generate;
use KSA\PasswordManager\Api\Node\Credential\Generate\Quality;
use KSA\PasswordManager\Api\Node\Delete;
use KSA\PasswordManager\Api\Node\Folder\CreateByPath;
use KSA\PasswordManager\Api\Node\Get as NodeGet;
use KSA\PasswordManager\Api\Node\GetByName;
use KSA\PasswordManager\Api\Node\Move;
use KSA\PasswordManager\Api\Node\Organization\Add as AddOrganization;
use KSA\PasswordManager\Api\Node\Pwned\Activate;
use KSA\PasswordManager\Api\Node\Pwned\ChartData;
use KSA\PasswordManager\Api\Node\Pwned\ChartDetailData;
use KSA\PasswordManager\Api\Node\Share\PublicShare;
use KSA\PasswordManager\Api\Node\Share\PublicShareSingle;
use KSA\PasswordManager\Api\Node\Share\Remove as RemoveShare;
use KSA\PasswordManager\Api\Node\Share\Share;
use KSA\PasswordManager\Api\Node\Share\ShareableUsers;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Middleware\NodeAccessMiddleware;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    CoreConfigProvider::ROUTES        => [
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_COMMENT_ADD
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, Add::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Add::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_COMMENT_GET_BY_NODE_ID
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, Get::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Get::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_COMMENT_REMOVE
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, Remove::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Remove::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_GENERATE_PASSWORD
            , IRoute::MIDDLEWARE => Generate::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Generate::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_GENERATE_QUALITY
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, Quality::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Quality::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, PublicShare::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => PublicShare::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, PublicShareSingle::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => PublicShareSingle::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_REMOVE
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, RemoveShare::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => RemoveShare::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, Share::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Share::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_ADD
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, AddOrganization::class]
            , IRoute::METHOD     => IVerb::PUT
            , IRoute::NAME       => AddOrganization::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_DELETE
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Delete::class]
            , IRoute::METHOD     => IVerb::DELETE
            , IRoute::NAME       => Delete::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_GET_BY_ID
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, NodeGet::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => NodeGet::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_GET_BY_NAME
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, GetByName::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => GetByName::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_MOVE
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Move::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Move::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_UPDATE
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Organization\Update::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Organization\Update::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Organization\Remove::class]
            , IRoute::METHOD     => IVerb::DELETE
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Organization\Remove::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_USERS_SHAREABLE
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, ShareableUsers::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => ShareableUsers::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_ADD
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Attachment\Add::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Attachment\Add::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_GET_BY_NODE_ID
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Attachment\Get::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Attachment\Get::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_REMOVE
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Attachment\Remove::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Attachment\Remove::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_UPDATE
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Update::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Update::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_UPDATE_AVATAR
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, Update::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Update::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_CREATE
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Create::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Create::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_CHART_ALL
            , IRoute::MIDDLEWARE => ChartData::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => ChartData::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_CHART_DETAIL
            , IRoute::MIDDLEWARE => ChartDetailData::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => ChartDetailData::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Credential\Password\Get::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\Password\Get::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_UPDATE
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Credential\Update::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\Update::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_CREATE
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Folder\Create::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Folder\Create::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_FOLDER_CREATE_BY_PATH
            , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, CreateByPath::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => CreateByPath::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_PASSWORD_UPDATE
            , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Credential\Password\Update::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\Password\Update::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_ACTIVATE
            , IRoute::MIDDLEWARE => Activate::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Activate::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ACTIVITY_GET
            , IRoute::MIDDLEWARE => \KSA\PasswordManager\Api\Node\Activity\Get::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Activity\Get::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET_VALUE
            , IRoute::MIDDLEWARE => GetValue::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => GetValue::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET
            , IRoute::MIDDLEWARE => \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Get::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Get::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_ADD
            , IRoute::MIDDLEWARE => \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Add::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Add::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_DELETE
            , IRoute::MIDDLEWARE => \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Delete::class
            , IRoute::METHOD     => IVerb::DELETE
            , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Delete::class
        ],
    ],
    CoreConfigProvider::PUBLIC_ROUTES => []
];