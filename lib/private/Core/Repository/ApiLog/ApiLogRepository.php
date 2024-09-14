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

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Instance\Request\ApiLog;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Instance\Request\ApiLogInterface;
use KSP\Core\Repository\ApiLog\IApiLogRepository;

final readonly class ApiLogRepository implements IApiLogRepository {

    public function __construct(
        private IBackend         $backend,
        private IDateTimeService $dateTimeService
    ) {
    }

    #[\Override]
    public function log(ApiLogInterface $request): ApiLogInterface {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert("`apilog`")
            ->values(
                [
                    "`id`"           => '?'
                    , "`request_id`" => '?'
                    , "`data`"       => '?'
                    , "`start`"      => '?'
                    , "`end`"        => '?'
                    , "`create_ts`"  => '?'
                ]
            )
            ->setParameter(0, $request->getId())
            ->setParameter(1, $request->getRequestId())
            ->setParameter(2, $request->getData())
            ->setParameter(3, $request->getStart()->format(\DateTimeInterface::ATOM))
            ->setParameter(4, $request->getEnd()->format(\DateTimeInterface::ATOM))
            ->setParameter(5, $this->dateTimeService->toYMDHIS($request->getCreateTs()))
            ->executeStatement();

        return $request;
    }

    #[\Override]
    public function getAll(): ArrayList {
        $userLogs     = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $logs         = $queryBuilder->select(
            [
                "`id`"
                , "`request_id`"
                , "`data`"
                , "`start`"
                , "`end`"
                , "`create_ts`"
            ]
        )
            ->from('`apilog`')
            ->fetchAllAssociative();

        foreach ($logs as $log) {
            $userLogs->add(
                new ApiLog(
                    $log['id'],
                    $log['request_id'],
                    $log['data'],
                    $this->dateTimeService->fromFormat((string) $log['start']),
                    $this->dateTimeService->fromFormat((string) $log['end']),
                    $this->dateTimeService->fromFormat((string) $log['create_ts'])
                )
            );
        }
        return $userLogs;
    }

}
