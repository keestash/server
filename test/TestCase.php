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
use KSA\Register\Entity\Register\Event\Type;
use KSA\Register\Event\UserRegisteredEvent;
use KSA\Settings\Service\IOrganizationService;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Integration\Core\Service\User\Repository\UserRepositoryServiceTest;
use KST\Service\Event\TestStartedEvent;
use Laminas\Config\Config;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Ramsey\Uuid\Uuid;

class TestCase extends FrameworkTestCase {

    private ServiceManager $serviceManager;
    private float          $start;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->start          = microtime(true);
        $this->serviceManager = require __DIR__ . '/config/service_manager.php';
        $eventService         = $this->serviceManager->get(IEventService::class);
        $config               = $this->serviceManager->get(Config::class);

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
        /** @var IEncryptionService $encryptionService */
        $encryptionService = $this->getService(IEncryptionService::class);
        /** @var ICredentialService $credentialService */
        $credentialService = $this->getService(ICredentialService::class);

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

        $secret = openssl_random_pseudo_bytes(32);

        // encrypting secret with user derivation
        $c   = $credentialService->createCredentialFromDerivation($user);
        $key = $encryptionService->encrypt($c, $secret);
        $eventService->execute(
            new UserRegisteredEvent(
                $user,
                base64_encode($key),
                Type::CLI,
                1
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

    #[\Override]
    protected function tearDown(): void {
        $end = microtime(true);
        /** @var Config $config */
        $config        = $this->getService(Config::class);
        $benchmarkFile = $config->get('benchmark_file');
        file_put_contents(
            $benchmarkFile,
            json_encode(
                [
                    'name'     => sprintf("%s::%s", static::class, $this->name()),
                    'start'    => $this->start,
                    'end'      => $end,
                    'duration' => $end - $this->start
                ]
            ) . "\n"
            , FILE_APPEND
        );
        parent::tearDown();
    }

    public function testByPassMeaninglessRestrictions(): void {
        // https://github.com/sebastianbergmann/phpunit/issues/5132
        $this->assertIsString('Because PHPUnit thinks it must dictate how I organize my tests, I had to switch from abstract to regular class and this test');
    }

}
