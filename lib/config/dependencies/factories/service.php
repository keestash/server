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

use Doctrine\DBAL\Connection;
use doganoo\DIP\DateTime\DateTimeService;
use doganoo\DIP\Object\String\StringService;
use GuzzleHttp\Client;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\App\AppService;
use Keestash\Core\Service\App\InstallerService;
use Keestash\Core\Service\App\LoaderService;
use Keestash\Core\Service\Cache\NullService;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\Config\IniConfigService;
use Keestash\Core\Service\Core\Access\AccessService;
use Keestash\Core\Service\Core\Environment\EnvironmentService;
use Keestash\Core\Service\Core\Exception\ExceptionHandlerService;
use Keestash\Core\Service\Core\Language\LanguageService;
use Keestash\Core\Service\Core\Locale\LocaleService;
use Keestash\Core\Service\CSV\CSVService;
use Keestash\Core\Service\Derivation\DerivationService;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\Encryption\Base64Service;
use Keestash\Core\Service\Encryption\Credential\DerivedCredentialService;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\Encryption\Mask\StringMaskService;
use Keestash\Core\Service\Encryption\Password\PasswordService;
use Keestash\Core\Service\Event\EventService;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\Icon\IconService;
use Keestash\Core\Service\File\Mime\MimeTypeService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\CORS\ProjectConfiguration;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\Input\SanitizerService;
use Keestash\Core\Service\HTTP\JWTService;
use Keestash\Core\Service\HTTP\ResponseService;
use Keestash\Core\Service\HTTP\Route\RouteService;
use Keestash\Core\Service\L10N\GetText;
use Keestash\Core\Service\LDAP\LDAPService;
use Keestash\Core\Service\Organization\OrganizationService;
use Keestash\Core\Service\Payment\DefaultPaymentService;
use Keestash\Core\Service\Permission\PermissionService;
use Keestash\Core\Service\Permission\RoleService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\Queue\QueueService;
use Keestash\Core\Service\ReflectionService;
use Keestash\Core\Service\Router\ApiRequestService;
use Keestash\Core\Service\Router\RouterService;
use Keestash\Core\Service\Router\VerificationService;
use Keestash\Core\Service\User\Event\Listener\ScheduleUserStateEventListener;
use Keestash\Core\Service\User\Repository\UserRepositoryService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Application;
use Keestash\Factory\Core\Builder\Validator\EmailValidatorFactory;
use Keestash\Factory\Core\Builder\Validator\PhoneValidatorFactory;
use Keestash\Factory\Core\Builder\Validator\UriValidatorFactory;
use Keestash\Factory\Core\Legacy\LegacyFactory;
use Keestash\Factory\Core\Logger\LoggerFactory;
use Keestash\Factory\Core\Repository\Instance\InstanceDBFactory;
use Keestash\Factory\Core\Service\App\AppServiceFactory;
use Keestash\Factory\Core\Service\App\InstallerServiceFactory;
use Keestash\Factory\Core\Service\App\LoaderServiceFactory;
use Keestash\Factory\Core\Service\Config\ConfigServiceFactory;
use Keestash\Factory\Core\Service\Core\Exception\ExceptionHandlerServiceFactory;
use Keestash\Factory\Core\Service\Core\Language\LanguageServiceFactory;
use Keestash\Factory\Core\Service\CSV\CSVServiceFactory;
use Keestash\Factory\Core\Service\Derivation\DerivationServiceFactory;
use Keestash\Factory\Core\Service\Email\EmailServiceFactory;
use Keestash\Factory\Core\Service\Encryption\Credential\DerivedCredentialServiceFactory;
use Keestash\Factory\Core\Service\Encryption\KeestashEncryptionServiceFactory;
use Keestash\Factory\Core\Service\Encryption\Key\KeyServiceFactory;
use Keestash\Factory\Core\Service\Encryption\Password\PasswordServiceFactory;
use Keestash\Factory\Core\Service\Event\EventServiceFactory;
use Keestash\Factory\Core\Service\File\FileServiceFactory;
use Keestash\Factory\Core\Service\File\RawFile\RawFileServiceFactory;
use Keestash\Factory\Core\Service\HTTP\CORS\ProjectConfigurationFactory;
use Keestash\Factory\Core\Service\HTTP\HTTPServiceFactory;
use Keestash\Factory\Core\Service\HTTP\JWTServiceFactory;
use Keestash\Factory\Core\Service\HTTP\ResponseServiceFactory;
use Keestash\Factory\Core\Service\HTTP\SanitizerServiceFactory;
use Keestash\Factory\Core\Service\LDAP\LDAPServiceFactory;
use Keestash\Factory\Core\Service\Organization\OrganizationServiceFactory;
use Keestash\Factory\Core\Service\Permission\PermissionServiceFactory;
use Keestash\Factory\Core\Service\Permission\RoleServiceFactory;
use Keestash\Factory\Core\Service\Phinx\MigratorFactory;
use Keestash\Factory\Core\Service\Queue\QueueServiceFactory;
use Keestash\Factory\Core\Service\Router\ApiRequestServiceFactory;
use Keestash\Factory\Core\Service\Router\RouterServiceFactory;
use Keestash\Factory\Core\Service\Router\VerificationFactory;
use Keestash\Factory\Core\Service\User\Event\Listener\ScheduleUserStateEventListenerListenerFactory;
use Keestash\Factory\Core\Service\User\Repository\UserRepositoryServiceFactory;
use Keestash\Factory\Core\Service\User\UserServiceFactory;
use Keestash\Factory\Queue\Handler\EventHandlerFactory;
use Keestash\Factory\ThirdParty\Doctrine\ConnectionFactory;
use Keestash\Factory\ThirdParty\doganoo\DateTimeServiceFactory;
use Keestash\Factory\ThirdParty\nikolaposa\RateLimit\FileRateLimiterFactory;
use Keestash\Queue\Handler\EventHandler;
use Keestash\ThirdParty\nikolaposa\RateLimit\FileRateLimiter;
use KSA\PasswordManager\Service\Node\Edge\EdgeService;
use Laminas\I18n\Validator\PhoneNumber as PhoneValidator;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Validator\EmailAddress as EmailValidator;
use Laminas\Validator\Uri as UriValidator;
use Psr\Log\LoggerInterface;

return [
    UserService::class                                        => UserServiceFactory::class
    , ConfigService::class                                    => ConfigServiceFactory::class
    , KeyService::class                                       => KeyServiceFactory::class
    , KeestashEncryptionService::class                        => KeestashEncryptionServiceFactory::class
    , FileService::class                                      => FileServiceFactory::class
    , RawFileService::class                                   => RawFileServiceFactory::class
    , DerivedCredentialService::class                         => DerivedCredentialServiceFactory::class
    , EmailService::class                                     => EmailServiceFactory::class
    , OrganizationService::class                              => OrganizationServiceFactory::class
    , InstallerService::class                                 => InstallerServiceFactory::class
    , HTTPService::class                                      => HTTPServiceFactory::class
    , \Keestash\Core\Service\Instance\InstallerService::class => \Keestash\Factory\Core\Service\Instance\InstallerServiceFactory::class
    , LanguageService::class                                  => LanguageServiceFactory::class
    , RouterService::class                                    => RouterServiceFactory::class
    , UserRepositoryService::class                            => UserRepositoryServiceFactory::class
    , \Keestash\Core\Service\File\Upload\FileService::class   => \Keestash\Factory\Core\Service\Upload\FileServiceFactory::class
    , SanitizerService::class                                 => SanitizerServiceFactory::class
    , ApiRequestService::class                                => ApiRequestServiceFactory::class
    , NullService::class                                      => InvokableFactory::class
    , ReflectionService::class                                => InvokableFactory::class
    , LocaleService::class                                    => InvokableFactory::class
    , EnvironmentService::class                               => InvokableFactory::class
    , AppService::class                                       => AppServiceFactory::class
    , EdgeService::class                                      => InvokableFactory::class
    , IconService::class                                      => InvokableFactory::class
    , PasswordService::class                                  => PasswordServiceFactory::class
    , IniConfigService::class                                 => InvokableFactory::class
    , StringService::class                                    => InvokableFactory::class
    , AccessService::class                                    => InvokableFactory::class
    , CSVService::class                                       => CSVServiceFactory::class
    , MimeTypeService::class                                  => InvokableFactory::class
    , QueueService::class                                     => QueueServiceFactory::class
    , RouteService::class                                     => InvokableFactory::class
    , LDAPService::class                                      => LDAPServiceFactory::class
    , Base64Service::class                                    => InvokableFactory::class
    , DefaultPaymentService::class                            => InvokableFactory::class
    , DerivationService::class                                => DerivationServiceFactory::class
    , PermissionService::class                                => PermissionServiceFactory::class
    , RoleService::class                                      => RoleServiceFactory::class
    , StringMaskService::class                                => InvokableFactory::class
    , ResponseService::class                                  => ResponseServiceFactory::class
    , ExceptionHandlerService::class                          => ExceptionHandlerServiceFactory::class
    , GetText::class                                          => InvokableFactory::class
    , HTMLPurifier::class                                     => InvokableFactory::class
    // ThirdParty
    , DateTimeService::class                                  => DateTimeServiceFactory::class
    , Connection::class                                       => ConnectionFactory::class
    , \doganoo\DIP\HTTP\HTTPService::class                    => InvokableFactory::class
    , Client::class                                           => InvokableFactory::class
    , FileRateLimiter::class                                  => FileRateLimiterFactory::class
    , ProjectConfiguration::class                             => ProjectConfigurationFactory::class

    , LoggerInterface::class                                  => LoggerFactory::class
    , Application::class                                      => LegacyFactory::class
    , EventService::class                                     => EventServiceFactory::class
    , LoaderService::class                                    => LoaderServiceFactory::class
    , VerificationService::class                              => VerificationFactory::class
    , InstanceDB::class                                       => InstanceDBFactory::class
    , Migrator::class                                         => MigratorFactory::class
    , JWTService::class                                       => JWTServiceFactory::class
    , EventHandler::class                                     => EventHandlerFactory::class

    // builder
    , EmailValidator::class                                   => EmailValidatorFactory::class
    , PhoneValidator::class                                   => PhoneValidatorFactory::class
    , UriValidator::class                                     => UriValidatorFactory::class

    , ScheduleUserStateEventListener::class                   => ScheduleUserStateEventListenerListenerFactory::class
];