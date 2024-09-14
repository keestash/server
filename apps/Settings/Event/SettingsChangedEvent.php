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

namespace KSA\Settings\Event;

use Keestash\Core\DTO\Event\Event;
use KSA\Settings\Entity\Setting;

class SettingsChangedEvent extends Event {

    public function __construct(
        private readonly Setting $setting
        , private readonly bool  $override = false
        , private readonly int   $priority = 99999999
    ) {
    }

    /**
     * @return Setting
     */
    public function getSetting(): Setting {
        return $this->setting;
    }

    /**
     * @return bool
     */
    public function isOverride(): bool {
        return $this->override;
    }

    /**
     * @return int
     */
    #[\Override]
    public function getPriority(): int {
        return $this->priority;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            'setting'    => $this->getSetting()
            , 'override' => $this->isOverride()
            , 'priority' => $this->getPriority()
        ];
    }

}