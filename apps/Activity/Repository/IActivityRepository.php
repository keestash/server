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

namespace KSA\Activity\Repository;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\Activity\Entity\IActivity;
use KSA\Activity\Exception\ActivityException;
use KSA\Activity\Exception\ActivityNotFoundException;

interface IActivityRepository {

    /**
     * @param string $activityId
     * @return IActivity
     * @throws ActivityException|ActivityNotFoundException
     */
    public function get(string $activityId): IActivity;

    public function insert(IActivity $activity): IActivity;

    public function remove(string $appId, string $referenceKey): void;

    public function getAll(string $appId, string $referenceKey): ArrayList;

}