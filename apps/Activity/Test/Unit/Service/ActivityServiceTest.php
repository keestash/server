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

namespace KSA\Activity\Test\Unit\Service;

use KSA\Activity\Repository\IActivityRepository;
use KSA\Activity\Service\IActivityService;
use KSA\Activity\Test\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class ActivityServiceTest extends TestCase {

    private IActivityService    $activityService;
    private IActivityRepository $activityRepository;

    protected function setUp(): void {
        parent::setUp();
        $this->activityService    = $this->getService(IActivityService::class);
        $this->activityRepository = $this->getService(IActivityRepository::class);
    }

    public function testInsertActivity(): void {
        $appId        = 'activity';
        $referenceKey = Uuid::uuid4()->toString();
        $description  = Uuid::uuid4()->toString();
        $this->activityRepository->remove($appId, $referenceKey);
        $this->activityService->insertActivityWithSingleMessage(
            $appId
            , $referenceKey
            , $description
        );

        $activities = $this->activityRepository->getAll($appId, $referenceKey);
        $this->assertTrue(1 === $activities->length());
        $this->assertTrue($activities->get(0)->getAppId() === $appId);
        $this->assertTrue($activities->get(0)->getReferenceKey() === $referenceKey);
        $this->assertTrue($activities->get(0)->getData()->get(0) === $description);
    }

//    public function testInsertActivity(): void {
//        $this->expectNotToPerformAssertions();
//        $this->activityService->insertActivityWithSingleMessage(
//            'activity'
//            , '12345'
//            , 'the description'
//        );
//    }

}