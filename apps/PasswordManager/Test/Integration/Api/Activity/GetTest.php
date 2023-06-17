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

namespace KSA\PasswordManager\Test\Integration\Api\Activity;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\Activity\Entity\Activity;
use KSA\Activity\Repository\IActivityRepository;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

class GetTest extends TestCase {

    public function testEmpty(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers = $this->login($user, $password);

        $response = $this->getApplication()->handle(
            $this->getRequest(
                IVerb::GET
                , $this->preparePath('passwordManager', 'blablub')
                , []
                , $user
                , $headers
            )
        );

        $body = json_decode((string) $response->getBody(), true);
        $this->assertTrue($response->getStatusCode() === IResponse::NOT_FOUND);
        $this->assertCount(0, $body);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testList(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $credential = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        /** @var IActivityRepository $activityRepository */
        $activityRepository = $this->getService(IActivityRepository::class);
        $activityString     = Uuid::uuid4()->toString();
        $data               = new ArrayList();
        $data->add($activityString);

        $activity = new Activity(
            Uuid::uuid4()->toString()
            , 'passwordManager'
            , (string) $credential->getNode()->getId()
            , $data
            , new DateTimeImmutable()
        );
        $activityRepository->insert($activity);

        $headers = $this->login($user, $password);

        $response = $this->getApplication()->handle(
            $this->getRequest(
                IVerb::GET
                , $this->preparePath($activity->getAppId(), $activity->getReferenceKey())
                , []
                , $user
                , $headers
            )
        );

        $body = json_decode((string) $response->getBody(), true);
        $this->assertTrue($response->getStatusCode() === IResponse::OK);
        $this->assertCount(1, $body);
        $this->assertArrayHasKey('activityList', $body);
        $this->assertTrue($body['activityList'][0]['activity_id'] === $activity->getActivityId());
        $this->assertTrue($body['activityList'][0]['app_id'] === $activity->getAppId());
        $this->assertTrue($body['activityList'][0]['reference_key'] === $activity->getReferenceKey());
        $this->assertTrue($body['activityList'][0]['data'][0] === $activity->getData()->get(0));
        $this->assertTrue(strtotime($body['activityList'][0]['create_ts']['date']) === $activity->getCreateTs()->getTimestamp());
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    private function preparePath(string $appId, string $referenceKey): string {
        $path = str_replace(':referenceKey', $referenceKey, ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ACTIVITY_GET);
        $path = str_replace(':appId', $appId, $path);
        return $path;
    }

    public function testWithNoAccess(): void {
        /** @var IActivityRepository $activityRepository */
        $activityRepository = $this->getService(IActivityRepository::class);
        $activityString     = Uuid::uuid4()->toString();
        $data               = new ArrayList();
        $data->add($activityString);

        $firstUser = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $password   = Uuid::uuid4()->toString();
        $secondUser = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $firstUser
            , $this->getRootFolder($firstUser)
        );

        $activity = new Activity(
            Uuid::uuid4()->toString()
            , 'passwordManager'
            , (string) $edge->getNode()->getId()
            , $data
            , new DateTimeImmutable()
        );
        $activityRepository->insert($activity);

        $headers  = $this->login($secondUser, $password);
        $response = $this->getApplication()->handle(
            $this->getRequest(
                IVerb::GET
                , $this->preparePath($activity->getAppId(), $activity->getReferenceKey())
                , []
                , $secondUser
                , $headers
            )
        );
        $this->assertStatusCode(IResponse::UNAUTHORIZED, $response);
    }

}