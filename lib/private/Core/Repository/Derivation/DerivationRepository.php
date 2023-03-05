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

namespace Keestash\Core\Repository\Derivation;

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Exception\Repository\NoRowsFoundException;
use Keestash\Exception\Repository\TooManyRowsException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Derivation\IDerivation;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use Psr\Log\LoggerInterface;

class DerivationRepository implements IDerivationRepository {

    public function __construct(
        private readonly IBackend           $backend
        , private readonly IDateTimeService $dateTimeService
        , private readonly LoggerInterface  $logger
    ) {
    }

    public function clear(IUser $user): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->delete('`derivation`')
            ->where('user_id = ?')
            ->setParameter(0, $user->getId())
            ->executeStatement();
    }

    public function add(IDerivation $derivation): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert("`derivation`")
            ->values(
                [
                    "`id`"           => '?'
                    , "`derivation`" => '?'
                    , "`user_id`"    => '?'
                    , "`create_ts`"  => '?'
                ]
            )
            ->setParameter(0, $derivation->getId())
            ->setParameter(1, $derivation->getDerived())
            ->setParameter(2, $derivation->getUser()->getId())
            ->setParameter(3, $this->dateTimeService->toYMDHIS($derivation->getCreateTs()))
            ->executeStatement();
    }

    /**
     * @param IUser $user
     * @return IDerivation
     * @throws NoRowsFoundException
     * @throws PasswordManagerException
     */
    public function get(IUser $user): IDerivation {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    '`id`'
                    , '`derivation`'
                    , '`user_id`'
                    , '`create_ts`'
                ]
            )
                ->from('`derivation`')
                ->where('`user_id` = ?')
                ->setParameter(0, $user->getId());
            $result          = $queryBuilder->executeQuery();
            $derivations     = $result->fetchAllNumeric();
            $derivationCount = count($derivations);

            if (0 === $derivationCount) {
                throw new NoRowsFoundException();
            }

//            if ($derivationCount > 1) {
//                throw new TooManyRowsException();
//            }

            return new Derivation(
                $derivations[0][0]
                , $user
                , $derivations[0][1]
                , $this->dateTimeService->fromFormat((string) $derivations[0][3])
            );
        } catch (Exception $exception) {
            $this->logger->error('error while getting app', ['exception' => $exception, 'user' => $user]);
            throw new PasswordManagerException();
        }
    }

    public function remove(IDerivation $derivation): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->delete('`derivation`')
            ->where('id = ?')
            ->setParameter(0, $derivation->getId())
            ->executeStatement();
    }

}