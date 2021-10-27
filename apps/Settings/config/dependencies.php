<?php
declare(strict_types=1);

use doganoo\DI\Encryption\User\IUserService;
use doganoo\DIP\Encryption\User\UserService;
use KSA\GeneralApi\Factory\Repository\DemoUsersRepositoryFactory;
use KSA\Settings\Api\Organization\Activate;
use KSA\Settings\Api\Organization\Add;
use KSA\Settings\Api\Organization\Get;
use KSA\Settings\Api\Organization\ListAll;
use KSA\Settings\Api\Organization\Update;
use KSA\Settings\Controller\Organization\Detail;
use KSA\Settings\Controller\SettingsController;
use KSA\Settings\Event\Listener\OrganizationAddedEventListener;
use KSA\Settings\Event\Listener\UserChangedListener;
use KSA\Settings\Factory\Api\Organization\ActivateFactory;
use KSA\Settings\Factory\Api\Organization\AddFactory;
use KSA\Settings\Factory\Api\Organization\GetFactory;
use KSA\Settings\Factory\Api\Organization\ListAllFactory;
use KSA\Settings\Factory\Api\Organization\UpdateFactory;
use KSA\Settings\Factory\Api\Organization\UserFactory;
use KSA\Settings\Factory\Controller\Organization\DetailFactory;
use KSA\Settings\Factory\Controller\SettingsControllerFactory;
use KSA\Settings\Factory\Event\Listener\OrganizationAddedEventListenerFactory;
use KSA\Settings\Factory\Event\Listener\UserChangedListenerFactory;
use KSA\Settings\Factory\Repository\OrganizationRepositoryFactory;
use KSA\Settings\Factory\Repository\OrganizationUserRepositoryFactory;
use KSA\Settings\Repository\DemoUsersRepository;
use KSA\Settings\Repository\IOrganizationRepository;
use KSA\Settings\Repository\IOrganizationUserRepository;
use KSA\Settings\Repository\OrganizationRepository;
use KSA\Settings\Repository\OrganizationUserRepository;
use KSA\Settings\Service\SettingService;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'factories' => [
        // service
        SettingService::class                      => InvokableFactory::class,
        UserService::class                         => InvokableFactory::class,

        // controller
        SettingsController::class                  => SettingsControllerFactory::class,
        Detail::class                              => DetailFactory::class,

        // api
        Activate::class                            => ActivateFactory::class,
        Add::class                                 => AddFactory::class,
        Get::class                                 => GetFactory::class,
        ListAll::class                             => ListAllFactory::class,
        Update::class                              => UpdateFactory::class,
        \KSA\Settings\Api\Organization\User::class => UserFactory::class,

        // repository
        OrganizationRepository::class              => OrganizationRepositoryFactory::class,
        OrganizationUserRepository::class          => OrganizationUserRepositoryFactory::class,
        DemoUsersRepository::class                 => DemoUsersRepositoryFactory::class,

        // event
        // ----- listener
        OrganizationAddedEventListener::class      => OrganizationAddedEventListenerFactory::class,
    ]
    , 'aliases' => [
        IOrganizationRepository::class     => OrganizationRepository::class,
        IOrganizationUserRepository::class => OrganizationUserRepository::class,
        IUserService::class                => UserService::class,
    ]
];