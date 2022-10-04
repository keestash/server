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

    private array             $data;
    private DateTimeInterface $openTs;

    public function __construct(array $data, DateTimeInterface $openTs) {
        $this->data   = $data;
        $this->openTs = $openTs;
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

    public function jsonSerialize(): array {
        return [
            'data'     => $this->getData()
            , 'openTs' => $this->getOpenTs()
        ];
    }

}