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

namespace KSA\Activity\Event;

use Keestash\Core\DTO\Event\Event;
use KSA\Activity\Entity\IActivity;

class ActivityTriggeredEvent extends Event {

    public function __construct(
        private readonly IActivity $activity
        , private readonly int     $priority = 99999999
    ) {
    }

    /**
     * @return IActivity
     */
    public function getActivity(): IActivity {
        return $this->activity;
    }

    /**
     * @return int
     */
    public function getPriority(): int {
        return $this->priority;
    }

    public function jsonSerialize(): array {
        return [
            'activity'   => $this->getActivity()
            , 'priority' => $this->getPriority()
        ];
    }

}