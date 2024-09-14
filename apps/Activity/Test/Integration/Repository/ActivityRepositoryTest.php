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

namespace KSA\Activity\Test\Integration\Repository;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\Activity\Entity\Activity;
use KSA\Activity\Exception\ActivityNotFoundException;
use KSA\Activity\Repository\IActivityRepository;
use KSA\Activity\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class ActivityRepositoryTest extends TestCase {

    private IActivityRepository $activityRepository;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->activityRepository = $this->getService(IActivityRepository::class);
    }

    public function testInsertGetRemove(): void {
        $descriptions = new ArrayList();
        $descriptions->add(Uuid::uuid4()->toString());
        $insertedActivity  = $this->activityRepository->insert(
            new Activity(
                Uuid::uuid4()->toString()
                , 'activity'
                , Uuid::uuid4()->toString()
                , $descriptions
                , new DateTimeImmutable()
            )
        );
        $retrievedActivity = $this->activityRepository->get($insertedActivity->getActivityId());
        $this->assertTrue(
            $insertedActivity->getActivityId() === $retrievedActivity->getActivityId()
        );
        $this->assertTrue(
            $retrievedActivity->getData()->get(0) === $insertedActivity->getData()->get(0)
        );
        $this->activityRepository->remove('activity', $insertedActivity->getReferenceKey());
        $this->expectException(ActivityNotFoundException::class);
        $this->activityRepository->get($insertedActivity->getActivityId());
    }

    public function testGetAll(): void {
        $appId        = 'activity';
        $referenceKey = Uuid::uuid4()->toString();
        $descriptions = [
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        ];

        foreach ($descriptions as $description) {
            $list = new ArrayList();
            $list->add($description);
            $this->activityRepository->insert(
                new Activity(
                    Uuid::uuid4()->toString()
                    , $appId
                    , $referenceKey
                    , $list
                    , new DateTimeImmutable()
                )
            );
        }

        $all = $this->activityRepository->getAll($appId, $referenceKey);
        $this->assertTrue(
            $all->length() === count($descriptions)
        );
    }

    public function testActivityNotFound(): void {
        $this->expectException(ActivityNotFoundException::class);
        $this->activityRepository->get(Uuid::uuid4()->toString());
    }

    public function testGetAllNotFound(): void {
        $this->expectException(ActivityNotFoundException::class);
        $this->activityRepository->getAll('blablablub', 'blublb');
    }

    public function testImplementsRepositoryInterface(): void {
        $this->assertInstanceOf(
            IActivityRepository::class
            , $this->activityRepository
        );
    }

}