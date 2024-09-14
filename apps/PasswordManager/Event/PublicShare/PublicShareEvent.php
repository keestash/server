<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\PasswordManager\Event\PublicShare;

use DateTimeInterface;
use Keestash\Core\DTO\Event\Event;

class PublicShareEvent extends Event {

    public function __construct(
        private readonly array               $data
        , private readonly DateTimeInterface $openTs
        , private readonly int               $priority = 99999999
    ) {
    }

    /**
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * @return DateTimeInterface
     */
    public function getOpenTs(): DateTimeInterface {
        return $this->openTs;
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
            'data'       => $this->getData()
            , 'openTs'   => $this->getOpenTs()
            , 'priority' => $this->getPriority()
        ];
    }

}