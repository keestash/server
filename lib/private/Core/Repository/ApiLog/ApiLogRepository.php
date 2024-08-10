<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace Keestash\Core\Repository\ApiLog;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Instance\Request\APIRequest;
use Keestash\Core\DTO\Token\NullToken;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Instance\Request\IAPIRequest;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;

final readonly class ApiLogRepository implements IApiLogRepository {

    public function __construct(
        private IBackend $backend
    ) {
    }

    public function log(IAPIRequest $request): IAPIRequest {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert("`apilog`")
            ->values(
                [
                    "`token_name`" => '?'
                    , "`token`"    => '?'
                    , "`user_id`"  => '?'
                    , "`start_ts`" => '?'
                    , "`end_ts`"   => '?'
                    , "`route`"    => '?'
                ]
            )
            ->setParameter(0, $request->getToken()->getName())
            ->setParameter(1, $request->getToken()->getValue())
            ->setParameter(2, $request->getToken()->getUser()->getId())
            ->setParameter(3, $request->getStart()->getTimestamp())
            ->setParameter(4, $request->getEnd()->getTimestamp())
            ->setParameter(5, $request->getRoute())
            ->executeStatement();

        $lastInsertId = (int) $this->backend->getConnection()->lastInsertId();

        if ($lastInsertId === 0) {
            throw new \Exception();
        }

        return $request;
    }

    public function removeForUser(IUser $user): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('apilog')
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->executeStatement() !== 0;
    }

    public function read(IUser $user): ArrayList {
        $userLogs     = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $users        = $queryBuilder->select(
            [
                "`id`"
                , "`token_name`"
                , "`token`"
                , "`user_id`"
                , "`start_ts`"
                , "`end_ts`"
                , "`route`"

            ]
        )
            ->from('apilog')
            ->where('user_id = ?')
            ->setParameter(0, $user->getId())
            ->fetchAllAssociative();

        foreach ($users as $user) {
            $userLogs->add(
                new APIRequest(
                    new NullToken(),
                    (new \DateTimeImmutable())->setTimestamp((int) $user['start_ts']),
                    (new \DateTimeImmutable())->setTimestamp((int) $user['end_ts']),
                    $user['token_name']
                )
            );
        }
        return $userLogs;
    }

    public function getAll(): ArrayList {
        $userLogs     = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $users        = $queryBuilder->select(
            [
                "`id`"
                , "`token_name`"
                , "`token`"
                , "`user_id`"
                , "`start_ts`"
                , "`end_ts`"
                , "`route`"

            ]
        )
            ->from('apilog')
            ->fetchAllAssociative();

        foreach ($users as $user) {
            $userLogs->add(
                new APIRequest(
                    new NullToken(),
                    (new \DateTimeImmutable())->setTimestamp((int) $user['start_ts']),
                    (new \DateTimeImmutable())->setTimestamp((int) $user['end_ts']),
                    $user['token_name']
                )
            );
        }
        return $userLogs;
    }

}
