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

namespace KSP\Core\Repository\Queue;

use Doctrine\DBAL\Exception;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use JsonException;
use Keestash\Exception\Queue\QueueException;
use Keestash\Exception\Queue\QueueNotCreatedException;
use Keestash\Exception\Queue\QueueNotDeletedException;
use Keestash\Exception\Queue\QueueNotUpdatedException;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Repository\IRepository;

interface IQueueRepository extends IRepository {

    /**
     * @return array
     * @throws QueueException
     */
    public function getQueue(): array;

    /**
     * @return array
     * @throws QueueException
     */
    public function getSchedulableMessages(): array;

    /**
     * @param ArrayList $messageList
     * @return void
     * @throws Exception
     * @throws JsonException
     */
    public function bulkInsert(ArrayList $messageList): void;

    public function connect(): void;

    public function disconnect(): void;

    /**
     * @param IMessage $message
     * @return IMessage
     * @throws QueueNotCreatedException
     */
    public function insert(IMessage $message): IMessage;

    /**
     * @param IMessage $message
     * @return void
     * @throws QueueNotDeletedException
     */
    public function delete(IMessage $message): void;

    /**
     * @param IMessage $message
     * @return IMessage
     * @throws QueueNotUpdatedException
     */
    public function update(IMessage $message): IMessage;

    /**
     * @param string $uuid
     * @param int    $attempts
     * @return void
     * @throws QueueNotUpdatedException
     */
    public function updateAttempts(string $uuid, int $attempts): void;

    /**
     * @param string $uuid
     * @return void
     * @throws QueueNotDeletedException
     */
    public function deleteByUuid(string $uuid): void;

    /**
     * @param string $uuid
     * @return array
     * @throws QueueException
     * @throws NoRowsFoundException
     */
    public function getByUuid(string $uuid): array;

}