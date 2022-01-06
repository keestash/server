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

use KSA\PasswordManager\Api\Comment\Add as AddComment;
use KSA\PasswordManager\Api\Comment\Get;
use KSA\PasswordManager\Api\Comment\Remove;
use KSA\PasswordManager\Api\Generate\Generate;
use KSA\PasswordManager\Api\Import\Import;
use KSA\PasswordManager\Api\Node\Attachment\Add;
use KSA\PasswordManager\Api\Node\Credential\Create;
use KSA\PasswordManager\Api\Node\Credential\Update;
use KSA\PasswordManager\Api\Node\Delete;
use KSA\PasswordManager\Api\Node\GetByName;
use KSA\PasswordManager\Api\Node\Move;
use KSA\PasswordManager\Api\Node\Organization\Add as AddNodeOrganization;
use KSA\PasswordManager\Api\Node\Organization\Update as UpdateNodeOrganization;
use KSA\PasswordManager\Api\Node\ShareableUsers;
use KSA\PasswordManager\Api\Share\PublicShare;
use KSA\PasswordManager\Api\Share\PublicShareSingle;
use KSA\PasswordManager\Api\Share\Share;
use KSA\PasswordManager\Command\Node\Credential\CreateCredential;
use KSA\PasswordManager\Command\Node\Folder\CreateFolder;
use KSA\PasswordManager\Controller\Attachment\View;
use KSA\PasswordManager\Controller\PublicShare\PublicShareController;
use KSA\PasswordManager\Event\Listener\AfterPasswordChanged;
use KSA\PasswordManager\Event\Listener\AfterRegistration;
use KSA\PasswordManager\Event\Listener\AfterRegistration\CreateStarterPassword;
use KSA\PasswordManager\Event\Listener\OrganizationChangeListener;
use KSA\PasswordManager\Event\Listener\PublicShare\RemoveExpired;
use KSA\PasswordManager\Factory\Api\Comment\AddFactory as AddCommentFactory;
use KSA\PasswordManager\Factory\Api\Comment\GetFactory;
use KSA\PasswordManager\Factory\Api\Comment\RemoveFactory;
use KSA\PasswordManager\Factory\Api\Generate\GenerateFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\CreateFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\Password\UpdateFactory as UpdatePasswordFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\UpdateFactory;
use KSA\PasswordManager\Factory\Api\Node\DeleteFactory;
use KSA\PasswordManager\Factory\Api\Node\GetByNameFactory;
use KSA\PasswordManager\Factory\Api\Node\MoveFactory;
use KSA\PasswordManager\Factory\Api\Node\Organization\AddFactory;
use KSA\PasswordManager\Factory\Api\Node\Organization\UpdateFactory as UpdateNodeOrganizationFactory;
use KSA\PasswordManager\Factory\Api\Node\ShareableUsersFactory;
use KSA\PasswordManager\Factory\Api\Share\PublicShareFactory;
use KSA\PasswordManager\Factory\Api\Share\PublicShareSingleFactory;
use KSA\PasswordManager\Factory\Api\Share\ShareFactory;
use KSA\PasswordManager\Factory\Command\CreateCredentialFactory;
use KSA\PasswordManager\Factory\Command\CreateFolderFactory;
use KSA\PasswordManager\Factory\Controller\Attachment\ViewFactory;
use KSA\PasswordManager\Factory\Controller\PasswordManager\ControllerFactory;
use KSA\PasswordManager\Factory\Event\Listener\AfterPasswordChangedListenerFactory;
use KSA\PasswordManager\Factory\Event\Listener\AfterRegistrationFactory;
use KSA\PasswordManager\Factory\Event\Listener\CreateStarterPasswordFactory;
use KSA\PasswordManager\Factory\Event\Listener\OrganizationAddListenerFactory;
use KSA\PasswordManager\Factory\Event\Listener\RemoveExpiredFactory;
use KSA\PasswordManager\Factory\Middleware\NodeAccessMiddlewareFactory;
use KSA\PasswordManager\Factory\Repository\CommentRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\FileRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\NodeRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\OrganizationRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\PublicShareRepositoryFactory;
use KSA\PasswordManager\Factory\Service\AccessServiceFactory;
use KSA\PasswordManager\Factory\Service\Encryption\EncryptionServiceFactory;
use KSA\PasswordManager\Factory\Service\Node\BreadCrumbService\BreadCrumbServiceFactory;
use KSA\PasswordManager\Factory\Service\Node\Credential\CredentialServiceFactory;
use KSA\PasswordManager\Factory\Service\Node\NodeServiceFactory;
use KSA\PasswordManager\Factory\Service\NodeEncryptionServiceFactory;
use KSA\PasswordManager\Middleware\NodeAccessMiddleware;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\OrganizationRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'factories' => [
        // api
        // ---- comment
        AddComment::class                                                 => AddCommentFactory::class,
        Get::class                                                        => GetFactory::class,
        Remove::class                                                     => RemoveFactory::class,

        // ---- generate
        Generate::class                                                   => GenerateFactory::class,

        // ---- PublicShare
        PublicShare::class                                                => PublicShareFactory::class,
        PublicShareSingle::class                                          => PublicShareSingleFactory::class,
        \KSA\PasswordManager\Api\Share\Remove::class                      => \KSA\PasswordManager\Factory\Api\Share\RemoveFactory::class,
        Share::class                                                      => ShareFactory::class,

        // ---- Node
        \KSA\PasswordManager\Api\Node\Get::class                          => \KSA\PasswordManager\Factory\Api\Node\GetFactory::class,
        GetByName::class                                                  => GetByNameFactory::class,
        Move::class                                                       => MoveFactory::class,
        ShareableUsers::class                                             => ShareableUsersFactory::class,
        Delete::class                                                     => DeleteFactory::class,

        // ---- Organization
        AddNodeOrganization::class                                        => AddFactory::class,
        UpdateNodeOrganization::class                                     => UpdateNodeOrganizationFactory::class,
        \KSA\PasswordManager\Api\Node\Organization\Remove::class          => \KSA\PasswordManager\Factory\Api\Node\Organization\RemoveFactory::class,

        // ---- Node
        // ---- ---- Attachment
        Add::class                                                        => \KSA\PasswordManager\Factory\Api\Node\Attachment\AddFactory::class,
        \KSA\PasswordManager\Api\Node\Attachment\Get::class               => \KSA\PasswordManager\Factory\Api\Node\Attachment\GetFactory::class,
        \KSA\PasswordManager\Api\Node\Attachment\Remove::class            => \KSA\PasswordManager\Factory\Api\Node\Attachment\RemoveFactory::class,

        // ---- Node
        // ---- ---- Avatar
        \KSA\PasswordManager\Api\Node\Avatar\Update::class                => \KSA\PasswordManager\Factory\Api\Node\Avatar\UpdateFactory::class,

        // ---- Node
        // ---- ---- Credential
        Create::class                                                     => CreateFactory::class,
        Update::class                                                     => UpdateFactory::class,

        // ---- Node
        // ---- ---- Credential
        // ---- ---- ---- Password
        \KSA\PasswordManager\Api\Node\Credential\Password\Get::class      => \KSA\PasswordManager\Factory\Api\Node\Credential\Password\GetFactory::class,
        \KSA\PasswordManager\Api\Node\Credential\Password\Update::class   => UpdatePasswordFactory::class,

        // ---- Node
        // ---- ---- Folder
        \KSA\PasswordManager\Api\Node\Folder\Create::class                => \KSA\PasswordManager\Factory\Api\Node\Folder\CreateFactory::class,

        // service
        NodeEncryptionService::class                                      => NodeEncryptionServiceFactory::class,
        EncryptionService::class                                          => EncryptionServiceFactory::class,
        NodeService::class                                                => NodeServiceFactory::class,
        BreadCrumbService::class                                          => BreadCrumbServiceFactory::class,
        CredentialService::class                                          => CredentialServiceFactory::class,
        ShareService::class                                               => InvokableFactory::class,
        AccessService::class                                              => AccessServiceFactory::class,

        // event
        // ---- listener
        CreateStarterPassword::class                                      => CreateStarterPasswordFactory::class,
        AfterRegistration::class                                          => AfterRegistrationFactory::class,
        AfterPasswordChanged::class                                       => AfterPasswordChangedListenerFactory::class,
        RemoveExpired::class                                              => RemoveExpiredFactory::class,
        OrganizationChangeListener::class                                 => OrganizationAddListenerFactory::class,

        // dependency
        NodeAccessMiddleware::class                                       => NodeAccessMiddlewareFactory::class,

        // command
        CreateFolder::class                                               => CreateFolderFactory::class,
        CreateCredential::class                                           => CreateCredentialFactory::class,

        // controller
        View::class                                                       => ViewFactory::class,
        \KSA\PasswordManager\Controller\PasswordManager\Controller::class => ControllerFactory::class,
        PublicShareController::class                                      => \KSA\PasswordManager\Factory\Controller\PublicShare\PublicShareFactory::class,

        // repository
        FileRepository::class                                             => FileRepositoryFactory::class,
        NodeRepository::class                                             => NodeRepositoryFactory::class,
        OrganizationRepository::class                                     => OrganizationRepositoryFactory::class,
        CommentRepository::class                                          => CommentRepositoryFactory::class,
        PublicShareRepository::class                                      => PublicShareRepositoryFactory::class,
    ]
];