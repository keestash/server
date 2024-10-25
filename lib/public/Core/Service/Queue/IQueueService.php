<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSP\Core\Service\Queue;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use JsonException;
use Keestash\Core\DTO\Queue\Message;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSP\Core\Service\IService;

interface IQueueService extends IService {

    public function getQueue(bool $forceAll = false): ArrayList;

    /**
     * @param string $uuid
     * @return Message
     * @throws JsonException
     * @throws NoRowsFoundException
     */
    public function getByUuid(string $uuid): Message;

    public function remove(string $uuid): void;

    public function updateAttempts(string $uuid, int $attempts): void;

}
