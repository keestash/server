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

namespace KSA\Instance\Repository;

use DateTime;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Exception\Repository\RowNotInsertedException;
use KSP\Core\Backend\IBackend;

final readonly class DemoUsersRepository {

    public function __construct(
        private IBackend         $backend,
        private IDateTimeService $dateTimeService
    ) {
    }

    public function add(string $email): string {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
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
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) {
            throw new RowNotInsertedException();
        }
        return $email;
    }

    public function hasEmailAddress(string $address): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'email'
            ]
        )
            ->from('demo_users')
            ->where('email = ?')
            ->setParameter(0, $address);
        $result       = $queryBuilder->executeQuery();
        $users        = $result->fetchAllNumeric();
        $userCount    = count($users);
        return $userCount > 0;
    }

}
