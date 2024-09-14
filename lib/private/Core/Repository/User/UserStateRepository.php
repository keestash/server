<?php
declare(strict_types=1);
/**
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

namespace Keestash\Core\Repository\User;

use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException;
use doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException;
use Keestash\Core\DTO\User\NullUserState;
use Keestash\Core\DTO\User\UserState;
use Keestash\Core\DTO\User\UserStateName;
use Keestash\Exception\User\State\UserStateException;
use Keestash\Exception\User\State\UserStateNotInsertedException;
use Keestash\Exception\User\State\UserStateNotRemovedException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use Psr\Log\LoggerInterface;

final readonly class UserStateRepository implements IUserStateRepository {

    public function __construct(
        private IBackend           $backend
        , private IUserRepository  $userRepository
        , private IDateTimeService $dateTimeService
        , private LoggerInterface  $logger
    ) {
    }

    /**
     * @param IUser $user
     * @return IUserState
     * @throws InvalidKeyTypeException
     * @throws UnsupportedKeyTypeException
     * @throws UserStateException
     */
    #[\Override]
    public function getByUser(IUser $user): IUserState {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder = $queryBuilder->select(
                [
                    'us.`id`'
                    , 'us.`user_id`'
                    , 'us.`state`'
                    , 'us.`valid_from`'
                    , 'us.`create_ts`'
                    , 'us.`state_hash`'
                ]
            )
                ->from('user_state', 'us');

            $queryBuilder = $queryBuilder->
            where('us.`user_id` = ?')
                ->setParameter(0, $user->getId());

            $userStates     = $queryBuilder->executeQuery()->fetchAllAssociative();
            $userStateCount = count($userStates);

            if ($userStateCount === 0) {
                return new NullUserState();
            }

            $row           = $userStates[0];
            $id            = $row["id"];
            $state         = $row["state"];
            $validFrom     = $row["valid_from"];
            $createTs      = $row["create_ts"];
            $userStateHash = $row["state_hash"];

            return new UserState(
                (int) $id,
                $user,
                UserStateName::from((string) $state),
                $this->dateTimeService->fromString((string) $validFrom),
                $this->dateTimeService->fromString((string) $createTs),
                (string) $userStateHash
            );
        } catch (Exception $exception) {
            $context            = ['exception' => $exception];
            $context['message'] = $exception->getMessage();
            $context['state']   = $user;

            $this->logger->error('error retrieving all users', $context);
            throw new UserStateException();
        }
    }

    #[\Override]
    public function getByHash(string $hash): IUserState {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder = $queryBuilder->select(
                [
                    'us.`id`'
                    , 'us.`user_id`'
                    , 'us.`state`'
                    , 'us.`valid_from`'
                    , 'us.`create_ts`'
                    , 'us.`state_hash`'
                ]
            )
                ->from('user_state', 'us');

            $queryBuilder = $queryBuilder->
            where('us.`state_hash` = ?')
                ->setParameter(0, $hash);

            $userStates     = $queryBuilder->executeQuery()->fetchAllAssociative();
            $userStateCount = count($userStates);

            if ($userStateCount === 0) {
                return new NullUserState();
            }

            $row           = $userStates[0];
            $id            = $row["id"];
            $userId        = $row["user_id"];
            $state         = $row["state"];
            $validFrom     = $row["valid_from"];
            $createTs      = $row["create_ts"];
            $userStateHash = $row["state_hash"];

            return new UserState(
                (int) $id,
                $this->userRepository->getUserById((string) $userId),
                UserStateName::from((string) $state),
                $this->dateTimeService->fromString((string) $validFrom),
                $this->dateTimeService->fromString((string) $createTs),
                (string) $userStateHash
            );
        } catch (Exception $exception) {
            $context            = ['exception' => $exception];
            $context['message'] = $exception->getMessage();
            $context['hash']    = $hash;

            $this->logger->error('error retrieving all users', $context);
            throw new UserStateException();
        }
    }

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateNotRemovedException
     */
    #[\Override]
    public function remove(IUser $user): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('user_state')
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('error removing user state', ['exception' => $exception]);
            throw new UserStateNotRemovedException();
        }
    }

    /**
     * @param IUser       $user
     * @param string      $state
     * @param string|null $hash
     * @return void
     * @throws UserStateNotInsertedException
     */
    #[\Override]
    public function insert(IUser $user, string $state, ?string $hash = null): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert('user_state')
                ->values(
                    [
                        'user_id'      => '?'
                        , 'state'      => '?'
                        , 'state_hash' => '?'
                        , 'valid_from' => '?'
                        , 'create_ts'  => '?'
                    ]
                )
                ->setParameter(0, $user->getId())
                ->setParameter(1, $state)
                ->setParameter(2, $hash)
                ->setParameter(
                    3
                    , $this->dateTimeService->toYMDHIS(new DateTimeImmutable())
                )
                ->setParameter(
                    4
                    , $this->dateTimeService->toYMDHIS(new DateTimeImmutable())
                )
                ->executeStatement();

            $lastInsertId = $this->backend->getConnection()->lastInsertId();
            if (false === is_numeric($lastInsertId)) {
                throw new UserStateNotInsertedException();
            }
        } catch (Exception $exception) {
            $this->logger->error('error inserting user state', ['exception' => $exception]);
            throw new UserStateNotInsertedException();
        }
    }

}
