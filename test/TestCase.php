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

namespace KST;

use DateTimeImmutable;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\Organization\Organization;
use KSA\Register\Event\UserRegistrationConfirmedEvent;
use KSA\Settings\Service\IOrganizationService;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Integration\Core\Service\User\Repository\UserRepositoryServiceTest;
use KST\Service\Event\TestStartedEvent;
use Laminas\Config\Config;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Ramsey\Uuid\Uuid;

abstract class TestCase extends FrameworkTestCase {

    private ServiceManager $serviceManager;
    private array          $performance = [];

    protected function setUp(): void {
        parent::setUp();
        $this->performance[static::class] = [
            'name'  => static::class,
            'start' => microtime(true),
            'end'   => 0
        ];
        $this->serviceManager             = require __DIR__ . '/config/service_manager.php';
        $eventService                     = $this->serviceManager->get(IEventService::class);
        $config                           = $this->serviceManager->get(Config::class);

        $eventService->registerAll($config->get(ConfigProvider::EVENTS)->toArray());
        $eventService->execute(new TestStartedEvent(new DateTimeImmutable()));
    }

    protected function getServiceManager(): ServiceManager {
        return $this->serviceManager;
    }

    protected function getService(string $name) {
        return $this->getServiceManager()->get($name);
    }

    protected function createUser(
        string   $name
        , string $password
        , bool   $locked = false
        , string $email = ''
    ): IUser {
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IEventService $eventService */
        $eventService = $this->getService(IEventService::class);

        if ($email === '') {
            $email = Uuid::uuid4() . '@keestash.com';
        }

        $user = $userRepositoryService->createUser(
            $userService->toNewUser(
                [
                    'user_name'    => $name
                    , 'email'      => $email
                    , 'last_name'  => UserRepositoryServiceTest::class
                    , 'first_name' => UserRepositoryServiceTest::class
                    , 'password'   => $password
                    , 'phone'      => '0049123456789'
                    , 'website'    => 'https://keestash.com'
                    , 'locked'     => $locked
                    , 'deleted'    => false
                ]
            )
        );

        $eventService->execute(
            new UserRegistrationConfirmedEvent(
                $user
                , 1
            )
        );

        return $user;
    }

    protected function createAndInsertOrganization(string $name): IOrganization {
        /** @var IOrganizationService $organizationService */
        $organizationService = $this->getService(IOrganizationService::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);

        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setName($name);
        $organization->setPassword(
            $userService->hashPassword(md5((string) time()))
        );
        return $organizationService->add($organization);
    }

    protected function removeUser(IUser $user): void {
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        $userRepositoryService->removeUser($user);
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->performance[static::class]['end'] = microtime(true);
    }

}
