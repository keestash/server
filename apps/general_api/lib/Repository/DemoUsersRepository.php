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

namespace KSA\GeneralApi\Repository;

use DateTime;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\Repository\AbstractRepository;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSP\Core\Backend\IBackend;

class DemoUsersRepository extends AbstractRepository {

    private IDateTimeService $dateTimeService;

    public function __construct(
        IBackend $backend
        , IDateTimeService $dateTimeService
    ) {
        parent::__construct($backend);

        $this->dateTimeService = $dateTimeService;
    }

    public function add(string $email): string {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->insert('demo_users')
            ->values(
                [
                    'email'       => '?'
                    , 'create_ts' => '?'
                ]
            )
            ->setParameter(0, $email)
            ->setParameter(1,
                $this->dateTimeService->toYMDHIS(
                    new DateTime()
                )
            )
            ->execute();

        $lastInsertId = $this->getDoctrineLastInsertId();

        if (null === $lastInsertId) {
            throw new GeneralApiException();
        }
        return $email;
    }

}