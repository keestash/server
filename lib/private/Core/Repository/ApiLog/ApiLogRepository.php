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

use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Instance\Request\IAPIRequest;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;

class ApiLogRepository implements IApiLogRepository {

    private IBackend $backend;

    public function __construct(IBackend $backend) {
        $this->backend = $backend;
    }

    public function log(IAPIRequest $request): ?int {
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
            ->setParameter(3, $request->getStart())
            ->setParameter(4, $request->getEnd())
            ->setParameter(5, $request->getRoute())
            ->execute();

        $lastInsertId = (int) $this->backend->getConnection()->lastInsertId();

        if (0 === $lastInsertId) return null;
        return $lastInsertId;
    }

    public function removeForUser(IUser $user): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('apilog')
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->execute() !== 0;
    }

}
