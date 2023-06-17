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

use DateTimeInterface;
use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Exception\Repository\Derivation\DerivationNotAddedException;
use Keestash\Exception\Repository\Derivation\DerivationNotDeletedException;
use Keestash\Exception\Repository\Derivation\DerivationNotFoundException;
use Keestash\Exception\Repository\NoRowsFoundException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Derivation\IDerivation;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Log\LoggerInterface;

class DerivationRepository implements IDerivationRepository {

    public function __construct(
        private readonly IBackend           $backend
        , private readonly IDateTimeService $dateTimeService
        , private readonly LoggerInterface  $logger
        , private readonly IUserRepository  $userRepository
    ) {
    }

    /**
     * @param IUser $user
     * @return void
     * @throws DerivationNotDeletedException
     * @throws Exception
     */
    public function clear(IUser $user): void {
        try {
            $this->backend->getConnection()->beginTransaction();
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('`derivation`')
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->executeStatement();
            $this->backend->getConnection()->commit();
        } catch (Exception $e) {
            $this->backend->getConnection()->rollBack();
            $this->logger->error('error while clearing derivation', ['exception' => $e, 'user' => $user->getId()]);
            throw new DerivationNotDeletedException();
        }
    }

    /**
     * @return void
     * @throws DerivationNotDeletedException
     * @throws Exception
     */
    public function clearAll(): void {
        try {
            $this->backend->getConnection()->beginTransaction();
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('`derivation`')
                ->executeStatement();
            $this->backend->getConnection()->commit();
        } catch (Exception $e) {
            $this->backend->getConnection()->rollBack();
            $this->logger->error('error while clearing derivation', ['exception' => $e]);
            throw new DerivationNotDeletedException();
        }
    }

    /**
     * @param IDerivation $derivation
     * @return void
     * @throws DerivationNotAddedException
     * @throws Exception
     */
    public function add(IDerivation $derivation): void {
        try {
            $this->backend->getConnection()->beginTransaction();
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
            $this->backend->getConnection()->commit();
        } catch (Exception $e) {
            $this->backend->getConnection()->rollBack();
            $this->logger->error('error while adding derivation', ['exception' => $e, 'derivation' => $derivation]);
            throw new DerivationNotAddedException();
        }
    }

    /**
     * @param IUser $user
     * @return IDerivation
     * @throws DerivationNotFoundException
     * @throws NoRowsFoundException
     */
    public function get(IUser $user): IDerivation {
        try {
            $this->backend->getConnection()->beginTransaction();
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
            $this->backend->getConnection()->commit();

            return new Derivation(
                $derivations[0][0]
                , $user
                , $derivations[0][1]
                , $this->dateTimeService->fromFormat((string) $derivations[0][3])
            );
        } catch (Exception $exception) {
            $this->backend->getConnection()->rollBack();
            $this->logger->error('error while getting derivation', ['exception' => $exception, 'user' => $user]);
            throw new DerivationNotFoundException();
        }
    }

    /**
     * @return ArrayList
     * @throws DerivationNotFoundException
     * @throws Exception
     */
    public function getAll(): ArrayList {
        try {
            $this->backend->getConnection()->beginTransaction();
            $list         = new ArrayList();
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    '`id`'
                    , '`derivation`'
                    , '`user_id`'
                    , '`create_ts`'
                ]
            )
                ->from('derivation', 'd');

            $result      = $queryBuilder->executeQuery();
            $derivations = $result->fetchAllAssociative();

            $this->backend->getConnection()->commit();

            foreach ($derivations as $row) {
                $list->add(
                    new Derivation(
                        $row['id']
                        , $this->userRepository->getUserById((string) $row['user_id'])
                        , $row['derivation']
                        , $this->dateTimeService->fromString((string) $row['create_ts'])
                    )
                );
            }

            return $list;
        } catch (Exception|UserNotFoundException $exception) {
            $this->backend->getConnection()->rollBack();
            $this->logger->error('error retrieving all derivations', ['exception' => $exception]);
            throw new DerivationNotFoundException();
        }
    }

    /**
     * @param IDerivation $derivation
     * @return void
     * @throws DerivationNotDeletedException
     * @throws Exception
     */
    public function remove(IDerivation $derivation): void {
        try {
            $this->backend->getConnection()->beginTransaction();
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('`derivation`')
                ->where('id = ?')
                ->setParameter(0, $derivation->getId())
                ->executeStatement();
            $this->backend->getConnection()->commit();
        } catch (Exception $e) {
            $this->backend->getConnection()->rollBack();
            $this->logger->error('error retrieving all derivations', ['exception' => $e]);
            throw new DerivationNotDeletedException();
        }
    }

    /**
     * @param DateTimeInterface $reference
     * @return ArrayList
     * @throws Exception
     * @throws UserNotFoundException
     */
    public function getOlderThan(DateTimeInterface $reference): ArrayList {
        $list         = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $derivations  = $queryBuilder
            ->select(
                [
                    '`id`'
                    , '`derivation`'
                    , '`user_id`'
                    , '`create_ts`'
                ]
            )
            ->from('`derivation`')
            ->andWhere('create_ts < ?')
            ->orWhere('create_ts IS NULL')
            ->setParameter(
                0
                , $this->dateTimeService->toYMDHIS($reference)
            );

        $derivations = $derivations->executeQuery()
            ->fetchAllNumeric();
        foreach ($derivations as $row) {
            $list->add(
                new Derivation(
                    $row['id']
                    , $this->userRepository->getUserById((string) $row['user_id'])
                    , $row['derivation']
                    , $this->dateTimeService->fromString((string) $row['create_ts'])
                )
            );
        }
        return $list;
    }

}