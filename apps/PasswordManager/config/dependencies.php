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

use Keestash\ConfigProvider;
use KSA\PasswordManager\Api\Node\Attachment\Add;
use KSA\PasswordManager\Api\Node\Attachment\Download;
use KSA\PasswordManager\Api\Node\Credential\AdditionalData\GetValue;
use KSA\PasswordManager\Api\Node\Credential\Comment\Add as AddComment;
use KSA\PasswordManager\Api\Node\Credential\Comment\Get;
use KSA\PasswordManager\Api\Node\Credential\Comment\Remove;
use KSA\PasswordManager\Api\Node\Credential\Create;
use KSA\PasswordManager\Api\Node\Credential\Generate\Generate;
use KSA\PasswordManager\Api\Node\Credential\Generate\Quality;
use KSA\PasswordManager\Api\Node\Credential\Update\Alpha;
use KSA\PasswordManager\Api\Node\Credential\Update\Update;
use KSA\PasswordManager\Api\Node\Delete;
use KSA\PasswordManager\Api\Node\Folder\CreateByPath;
use KSA\PasswordManager\Api\Node\Get\Beta;
use KSA\PasswordManager\Api\Node\GetByName;
use KSA\PasswordManager\Api\Node\Move;
use KSA\PasswordManager\Api\Node\Organization\Add as AddNodeOrganization;
use KSA\PasswordManager\Api\Node\Organization\Update as UpdateNodeOrganization;
use KSA\PasswordManager\Api\Node\Pwned\ChangeState;
use KSA\PasswordManager\Api\Node\Pwned\ChartData;
use KSA\PasswordManager\Api\Node\Pwned\IsActive;
use KSA\PasswordManager\Api\Node\Search;
use KSA\PasswordManager\Api\Node\Share\Public\PublicShare;
use KSA\PasswordManager\Api\Node\Share\Public\PublicShareSingle;
use KSA\PasswordManager\Api\Node\Share\Regular\Share;
use KSA\PasswordManager\Api\Node\Share\Regular\ShareableUsers;
use KSA\PasswordManager\Command\Node\Credential\CreateCredential;
use KSA\PasswordManager\Command\Node\Dump;
use KSA\PasswordManager\Command\Node\DumpAll;
use KSA\PasswordManager\Command\Node\Folder\CreateFolder;
use KSA\PasswordManager\Command\Node\ImportPwned;
use KSA\PasswordManager\Event\Listener\AfterPasswordChanged;
use KSA\PasswordManager\Event\Listener\AfterRegistration;
use KSA\PasswordManager\Event\Listener\BreachesListener;
use KSA\PasswordManager\Event\Listener\CredentialChangedListener;
use KSA\PasswordManager\Event\Listener\NodeRemovedEventListener;
use KSA\PasswordManager\Event\Listener\OrganizationChangeListener;
use KSA\PasswordManager\Event\Listener\PasswordsListener;
use KSA\PasswordManager\Event\Listener\RemoveExpiredPublicShare;
use KSA\PasswordManager\Factory\Api\Node\Attachment\DownloadFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\AdditionalData\GetValueFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\Comment\AddFactory as AddCommentFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\Comment\GetFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\Comment\RemoveFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\CreateFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\Generate\GenerateFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\Generate\QualityFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\Password\UpdateFactory as UpdatePasswordFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\Update\AlphaFactory;
use KSA\PasswordManager\Factory\Api\Node\Credential\UpdateFactory;
use KSA\PasswordManager\Factory\Api\Node\DeleteFactory;
use KSA\PasswordManager\Factory\Api\Node\Folder\CreateByPathFactory;
use KSA\PasswordManager\Factory\Api\Node\Get\BetaFactory;
use KSA\PasswordManager\Factory\Api\Node\GetByNameFactory;
use KSA\PasswordManager\Factory\Api\Node\MoveFactory;
use KSA\PasswordManager\Factory\Api\Node\Organization\AddFactory;
use KSA\PasswordManager\Factory\Api\Node\Organization\UpdateFactory as UpdateNodeOrganizationFactory;
use KSA\PasswordManager\Factory\Api\Node\Pwned\ChangeStateFactory;
use KSA\PasswordManager\Factory\Api\Node\Pwned\ChartDataFactory;
use KSA\PasswordManager\Factory\Api\Node\Pwned\IsActiveFactory;
use KSA\PasswordManager\Factory\Api\Node\SearchFactory;
use KSA\PasswordManager\Factory\Api\Node\Share\PublicShareFactory;
use KSA\PasswordManager\Factory\Api\Node\Share\PublicShareSingleFactory;
use KSA\PasswordManager\Factory\Api\Node\Share\ShareFactory;
use KSA\PasswordManager\Factory\Api\Node\ShareableUsersFactory;
use KSA\PasswordManager\Factory\Command\CreateCredentialFactory;
use KSA\PasswordManager\Factory\Command\CreateFolderFactory;
use KSA\PasswordManager\Factory\Command\DumpAllFactory;
use KSA\PasswordManager\Factory\Command\DumpFactory;
use KSA\PasswordManager\Factory\Command\ImportPwnedFactory;
use KSA\PasswordManager\Factory\Event\Listener\AfterPasswordChangedListenerFactory;
use KSA\PasswordManager\Factory\Event\Listener\AfterRegistrationFactory;
use KSA\PasswordManager\Factory\Event\Listener\BreachesListenerFactory;
use KSA\PasswordManager\Factory\Event\Listener\CredentialChangedListenerFactory;
use KSA\PasswordManager\Factory\Event\Listener\NodeRemovedEventListenerFactory;
use KSA\PasswordManager\Factory\Event\Listener\OrganizationAddListenerFactory;
use KSA\PasswordManager\Factory\Event\Listener\PasswordsListenerFactory;
use KSA\PasswordManager\Factory\Event\Listener\RemoveExpiredFactory;
use KSA\PasswordManager\Factory\Middleware\NodeAccessMiddlewareFactory;
use KSA\PasswordManager\Factory\Repository\CommentRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\Credential\AdditionalData\AdditionalDataRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\FileRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\NodeRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\OrganizationRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\PwnedBreachesRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\Node\PwnedPasswordsRepositoryFactory;
use KSA\PasswordManager\Factory\Repository\PublicShareRepositoryFactory;
use KSA\PasswordManager\Factory\Service\AccessServiceFactory;
use KSA\PasswordManager\Factory\Service\Encryption\EncryptionServiceFactory;
use KSA\PasswordManager\Factory\Service\Node\BreadCrumbService\BreadCrumbServiceFactory;
use KSA\PasswordManager\Factory\Service\Node\Credential\CredentialServiceFactory;
use KSA\PasswordManager\Factory\Service\Node\NodeServiceFactory;
use KSA\PasswordManager\Factory\Service\Node\PwnedServiceFactory;
use KSA\PasswordManager\Factory\Service\Node\Share\ShareServiceFactory;
use KSA\PasswordManager\Middleware\NodeAccessMiddleware;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\Credential\AdditionalData\AdditionalDataRepository;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\OrganizationRepository;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSA\PasswordManager\Service\Node\PwnedService;
use KSA\PasswordManager\Service\Node\Share\ShareService;

return [
    ConfigProvider::FACTORIES => [
        // api
        // ---- comment
        AddComment::class                                                     => AddCommentFactory::class
        , Get::class                                                          => GetFactory::class
        , Remove::class                                                       => RemoveFactory::class

        // ---- generate
        , Generate::class                                                     => GenerateFactory::class
        , Quality::class                                                      => QualityFactory::class

        // ---- PublicShare
        , PublicShare::class                                                  => PublicShareFactory::class
        , PublicShareSingle::class                                            => PublicShareSingleFactory::class
        , \KSA\PasswordManager\Api\Node\Share\Regular\Remove::class           => \KSA\PasswordManager\Factory\Api\Node\Share\RemoveFactory::class
        , Share::class                                                        => ShareFactory::class

        // ---- Node
        , \KSA\PasswordManager\Api\Node\Get\Get::class                        => \KSA\PasswordManager\Factory\Api\Node\Get\GetFactory::class
        , Beta::class                                                         => BetaFactory::class
        , GetByName::class                                                    => GetByNameFactory::class
        , Move::class                                                         => MoveFactory::class
        , ShareableUsers::class                                               => ShareableUsersFactory::class
        , Delete::class                                                       => DeleteFactory::class
        , \KSA\PasswordManager\Api\Node\Folder\Update::class                  => \KSA\PasswordManager\Factory\Api\Node\UpdateFactory::class

        // ---- Node
        // ---- ---- Pwned
        , ChartData::class                                                    => ChartDataFactory::class,
        ChangeState::class                                                    => ChangeStateFactory::class,
        IsActive::class                                                       => IsActiveFactory::class,

        // ---- Organization
        AddNodeOrganization::class                                            => AddFactory::class,
        UpdateNodeOrganization::class                                         => UpdateNodeOrganizationFactory::class,
        \KSA\PasswordManager\Api\Node\Organization\Remove::class              => \KSA\PasswordManager\Factory\Api\Node\Organization\RemoveFactory::class,

        // ---- Node
        // ---- ---- Attachment
        Add::class                                                            => \KSA\PasswordManager\Factory\Api\Node\Attachment\AddFactory::class,
        \KSA\PasswordManager\Api\Node\Attachment\Get::class                   => \KSA\PasswordManager\Factory\Api\Node\Attachment\GetFactory::class,
        \KSA\PasswordManager\Api\Node\Attachment\Remove::class                => \KSA\PasswordManager\Factory\Api\Node\Attachment\RemoveFactory::class,
        Download::class                                                       => DownloadFactory::class,

        // ---- Node
        // ---- ---- Activity
        \KSA\PasswordManager\Api\Node\Activity\Get::class                     => \KSA\PasswordManager\Factory\Api\Node\Activity\GetFactory::class,

        // ---- Node
        // ---- ---- Credential
        Create::class                                                         => CreateFactory::class,
        Update::class                                                         => UpdateFactory::class,
        Alpha::class                                                          => AlphaFactory::class,
        \KSA\PasswordManager\Api\Node\Credential\Update\Beta::class           => \KSA\PasswordManager\Factory\Api\Node\Credential\Update\BetaFactory::class,

        // ---- Node
        // ---- ---- Credential
        // ---- ---- ---- Password
        \KSA\PasswordManager\Api\Node\Credential\Password\Get::class          => \KSA\PasswordManager\Factory\Api\Node\Credential\Password\GetFactory::class,
        \KSA\PasswordManager\Api\Node\Credential\Password\Update::class       => UpdatePasswordFactory::class,

        // ---- Node
        // ---- ---- Credential
        // ---- ---- ---- Password
        \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Get::class    => \KSA\PasswordManager\Factory\Api\Node\Credential\AdditionalData\GetFactory::class,
        \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Delete::class => \KSA\PasswordManager\Factory\Api\Node\Credential\AdditionalData\DeleteFactory::class,
        GetValue::class                                                       => GetValueFactory::class,
        \KSA\PasswordManager\Api\Node\Credential\AdditionalData\Add::class    => \KSA\PasswordManager\Factory\Api\Node\Credential\AdditionalData\AddFactory::class,

        // ---- Node
        // ---- ---- Folder
        \KSA\PasswordManager\Api\Node\Folder\Create::class                    => \KSA\PasswordManager\Factory\Api\Node\Folder\CreateFactory::class,
        CreateByPath::class                                                   => CreateByPathFactory::class,

        // service
        EncryptionService::class                                              => EncryptionServiceFactory::class,
        NodeService::class                                                    => NodeServiceFactory::class,
        BreadCrumbService::class                                              => BreadCrumbServiceFactory::class,
        CredentialService::class                                              => CredentialServiceFactory::class,
        ShareService::class                                                   => ShareServiceFactory::class,
        AccessService::class                                                  => AccessServiceFactory::class,
        PwnedService::class                                                   => PwnedServiceFactory::class,

        // event
        // ---- listener
        AfterRegistration::class                                              => AfterRegistrationFactory::class,
        AfterPasswordChanged::class                                           => AfterPasswordChangedListenerFactory::class,
        RemoveExpiredPublicShare::class                                       => RemoveExpiredFactory::class,
        OrganizationChangeListener::class                                     => OrganizationAddListenerFactory::class,
        CredentialChangedListener::class                                      => CredentialChangedListenerFactory::class,
        NodeRemovedEventListener::class                                       => NodeRemovedEventListenerFactory::class,
        BreachesListener::class                                               => BreachesListenerFactory::class,
        PasswordsListener::class                                              => PasswordsListenerFactory::class,

        // dependency
        NodeAccessMiddleware::class                                           => NodeAccessMiddlewareFactory::class,

        // command
        CreateFolder::class                                                   => CreateFolderFactory::class,
        CreateCredential::class                                               => CreateCredentialFactory::class,
        Dump::class                                                           => DumpFactory::class,
        DumpAll::class                                                        => DumpAllFactory::class,
        ImportPwned::class                                                    => ImportPwnedFactory::class,

        // repository
        PublicShareRepository::class                                          => PublicShareRepositoryFactory::class,
        CommentRepository::class                                              => CommentRepositoryFactory::class,

        // repository
        // ---- node
        FileRepository::class                                                 => FileRepositoryFactory::class,
        NodeRepository::class                                                 => NodeRepositoryFactory::class,
        OrganizationRepository::class                                         => OrganizationRepositoryFactory::class,
        PwnedPasswordsRepository::class                                       => PwnedPasswordsRepositoryFactory::class,
        PwnedBreachesRepository::class                                        => PwnedBreachesRepositoryFactory::class,
        Search::class                                                         => SearchFactory::class,

        // repository
        // ---- node
        // ---- ---- credential
        // ---- ---- ---- credential
        AdditionalDataRepository::class                                       => AdditionalDataRepositoryFactory::class
    ],

];
