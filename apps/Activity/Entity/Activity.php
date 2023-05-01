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

namespace KSA\Activity\Entity;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;

class Activity implements IActivity {

    public function __construct(
        private readonly string            $activityId,
        private readonly string            $appId,
        private readonly string            $referenceKey,
        private readonly ArrayList         $data,
        private readonly DateTimeInterface $createTs
    ) {
    }

    /**
     * @return string
     */
    public function getActivityId(): string {
        return $this->activityId;
    }

    /**
     * @return string
     */
    public function getAppId(): string {
        return $this->appId;
    }

    /**
     * @return string
     */
    public function getReferenceKey(): string {
        return $this->referenceKey;
    }

    /**
     * @return ArrayList
     */
    public function getData(): ArrayList {
        return $this->data;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }


    public function jsonSerialize(): array {
        return [
            'activity_id'     => $this->getActivityId()
            , 'app_id'        => $this->getAppId()
            , 'reference_key' => $this->getReferenceKey()
            , 'data'          => $this->getData()->toArray()
            , 'create_ts'     => $this->getCreateTs()
        ];
    }

}