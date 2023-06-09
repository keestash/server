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

class ReferenceRemovedEvent extends Event {

    public function __construct(private readonly string $appId, private readonly string $referenceKey) {
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

    public function jsonSerialize(): array {
        return [
            'appId'          => $this->getAppId()
            , 'referenceKey' => $this->getReferenceKey()
        ];
    }

}