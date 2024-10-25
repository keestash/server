<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

use Keestash\Core\Backend\SQLBackend\MySQLBackend;
use Keestash\Core\Repository\ApiLog\ApiLogRepository;
use Keestash\Core\Repository\AppRepository\AppRepository;
use Keestash\Core\Repository\EncryptionKey\Organization\OrganizationKeyRepository;
use Keestash\Core\Repository\EncryptionKey\User\UserKeyRepository;
use Keestash\Core\Repository\File\FileRepository;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Repository\Job\JobRepository;
use Keestash\Core\Repository\LDAP\DefaultConnectionRepository;
use Keestash\Core\Repository\LDAP\DefaultLDAPRepository;
use Keestash\Core\Repository\MailLog\MailLogRepository;
use Keestash\Core\Repository\Payment\DefaultPaymentLogRepository;
use Keestash\Core\Repository\Queue\QueueRepository;
use Keestash\Core\Repository\RBAC\RBACRepository;
use Keestash\Core\Repository\Token\TokenRepository;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Repository\User\UserStateRepository;
use Keestash\Factory\Core\Backend\MySQLBackendFactory;
use Keestash\Factory\Core\Repository\ApiLogRepository\ApiLogRepositoryFactory;
use Keestash\Factory\Core\Repository\AppRepository\AppRepositoryFactory;
use Keestash\Factory\Core\Repository\EncryptionKey\Organization\OrganizationKeyRepositoryFactory;
use Keestash\Factory\Core\Repository\EncryptionKey\User\UserKeyRepositoryFactory;
use Keestash\Factory\Core\Repository\File\FileRepositoryFactory;
use Keestash\Factory\Core\Repository\Instance\InstanceRepositoryFactory;
use Keestash\Factory\Core\Repository\Job\JobRepositoryFactory;
use Keestash\Factory\Core\Repository\MailLog\MailLogRepositoryFactory;
use Keestash\Factory\Core\Repository\Queue\QueueRepositoryFactory;
use Keestash\Factory\Core\Repository\RBAC\PermissionRepositoryFactory;
use Keestash\Factory\Core\Repository\Token\TokenRepositoryFactory;
use Keestash\Factory\Core\Repository\User\UserRepositoryFactory;
use Keestash\Factory\Core\Repository\User\UserStateRepositoryFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    ApiLogRepository::class              => ApiLogRepositoryFactory::class
    , MySQLBackend::class                => MySQLBackendFactory::class
    , FileRepository::class              => FileRepositoryFactory::class
    , UserRepository::class              => UserRepositoryFactory::class
    , UserKeyRepository::class           => UserKeyRepositoryFactory::class
    , OrganizationKeyRepository::class   => OrganizationKeyRepositoryFactory::class
    , UserStateRepository::class         => UserStateRepositoryFactory::class
    , InstanceRepository::class          => InstanceRepositoryFactory::class
    , TokenRepository::class             => TokenRepositoryFactory::class
    , AppRepository::class               => AppRepositoryFactory::class
    , QueueRepository::class             => QueueRepositoryFactory::class
    , RBACRepository::class              => PermissionRepositoryFactory::class
    , DefaultLDAPRepository::class       => InvokableFactory::class
    , DefaultConnectionRepository::class => InvokableFactory::class
    , DefaultPaymentLogRepository::class => InvokableFactory::class
    , MailLogRepository::class           => MailLogRepositoryFactory::class
    , JobRepository::class               => JobRepositoryFactory::class
];
