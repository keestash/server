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
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\User\UserState;
use Keestash\Exception\User\State\UserStateException;
use Keestash\Exception\User\State\UserStateNotInsertedException;
use Keestash\Exception\User\State\UserStateNotRemovedException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use Psr\Log\LoggerInterface as ILogger;

class UserStateRepository implements IUserStateRepository {

    private IUserRepository  $userRepository;
    private IDateTimeService $dateTimeService;
    private IBackend         $backend;
    private ILogger          $logger;

    public function __construct(
        IBackend           $backend
        , IUserRepository  $userRepository
        , IDateTimeService $dateTimeService
        , ILogger          $logger
    ) {
        $this->backend         = $backend;
        $this->userRepository  = $userRepository;
        $this->dateTimeService = $dateTimeService;
        $this->logger          = $logger;
    }

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotRemovedException
     */
    public function unlock(IUser $user): void {
        if (false === $this->isLocked($user)) {
            throw new UserStateException();
        }
        $this->remove($user, IUserState::USER_STATE_LOCK);
    }

    /**
     * @param IUser $user
     * @return bool
     */
    private function isLocked(IUser $user): bool {
        $lockedUsers = $this->getLockedUsers();

        /** @var IUserState $userState */
        foreach ($lockedUsers->toArray() as $userState) {
            if (
                $user->getId() === $userState->getUser()->getId()
                && $userState->getState() === IUserState::USER_STATE_LOCK
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return HashTable
     * @throws UserStateException
     */
    public function getLockedUsers(): HashTable {
        return $this->getAll(IUserState::USER_STATE_LOCK);
    }

    /**
     * @param string|null $state
     * @return HashTable
     * @throws UserStateException
     */
    private function getAll(?string $state = null): HashTable {
        try {
            $table = new HashTable();

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

            if ($state !== null) {
                $queryBuilder = $queryBuilder->
                where('us.`state` = ?')
                    ->setParameter(0, $state);
            }

            $usersStates = $queryBuilder->executeQuery()->fetchAllAssociative();

            foreach ($usersStates as $row) {
                $id            = $row["id"];
                $userId        = $row["user_id"];
                $state         = $row["state"];
                $validFrom     = $row["valid_from"];
                $createTs      = $row["create_ts"];
                $userStateHash = $row["state_hash"];

                $user = $this->userRepository->getUserById((string) $userId);

                $userState = new UserState();
                $userState->setId((int) $id);
                $userState->setUser($user);
                $userState->setValidFrom(
                    $this->dateTimeService->fromString((string) $validFrom)
                );
                $userState->setCreateTs(
                    $this->dateTimeService->fromString((string) $createTs)
                );
                $userState->setState((string) $state);
                $userState->setStateHash((string) $userStateHash);

                $table->put($userState->getId(), $userState);
            }

            return $table;
        } catch (Exception|UserNotFoundException $exception) {
            $context            = ['exception' => $exception];
            $context['message'] = $exception->getMessage();
            $context['sql']     = $queryBuilder->getSQL();
            $context['state']   = $state;

            $this->logger->error('error retrieving all users', $context);
            throw new UserStateException();
        }
    }

    /**
     * @param IUser  $user
     * @param string $state
     * @return void
     * @throws UserStateNotRemovedException
     */
    private function remove(IUser $user, string $state): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('user_state')
                ->where('user_id = ?')
                ->andWhere('state = ?')
                ->setParameter(0, $user->getId())
                ->setParameter(1, $state)
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('error removing user state', ['exception' => $exception]);
            throw new UserStateNotRemovedException();
        }
    }

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotInsertedException
     */
    public function lock(IUser $user): void {
        if (true === $this->isLocked($user)) {
            throw new UserStateException('user is already locked');
        }
        $this->insert(
            $user
            , IUserState::USER_STATE_LOCK
        );
    }

    /**
     * @param IUser       $user
     * @param string      $state
     * @param string|null $hash
     * @return void
     * @throws UserStateNotInsertedException
     */
    private function insert(IUser $user, string $state, ?string $hash = null): void {
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

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotInsertedException
     * TODO check whether already exists
     */
    public function delete(IUser $user): void {
        if (true === $this->isDeleted($user)) {
            throw new UserStateException();
        }
        $this->insert(
            $user
            , IUserState::USER_STATE_LOCK
        );
        $this->insert(
            $user
            , IUserState::USER_STATE_DELETE
        );
    }

    /**
     * @param IUser $user
     * @return bool
     * @throws InvalidKeyTypeException
     * @throws UnsupportedKeyTypeException
     */
    private function isDeleted(IUser $user): bool {
        $deletedUsers = $this->getDeletedUsers();

        foreach ($deletedUsers->keySet() as $key) {
            /** @var IUserState $userState */
            $userState = $deletedUsers->get($key);
            if ($user->getId() === $userState->getUser()->getId()) return true;
        }
        return false;
    }

    /**
     * @return HashTable
     * @throws UserStateException
     */
    public function getDeletedUsers(): HashTable {
        return $this->getAll(IUserState::USER_STATE_DELETE);
    }

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotRemovedException
     */
    public function revertDelete(IUser $user): void {
        if (false === $this->isDeleted($user)) {
            throw new UserStateException();
        }
        $this->remove($user, IUserState::USER_STATE_DELETE);
    }

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateNotRemovedException
     */
    public function removeAll(IUser $user): void {
        $this->remove(
            $user
            , IUserState::USER_STATE_LOCK
        );
        $this->remove(
            $user
            , IUserState::USER_STATE_LOCK
        );
        $this->remove(
            $user
            , IUserState::USER_STATE_REQUEST_PW_CHANGE
        );
    }

    /**
     * @param IUser  $user
     * @param string $hash
     * @return void
     * @throws UserStateException
     * @throws UserStateNotInsertedException
     */
    public function requestPasswordReset(IUser $user, string $hash): void {
        if (true === $this->hasPasswordResetRequested($user)) {
            throw new UserStateException();
        }
        $this->insert(
            $user
            , IUserState::USER_STATE_REQUEST_PW_CHANGE
            , $hash
        );

    }

    /**
     * @param IUser $user
     * @return bool
     * @throws InvalidKeyTypeException
     * @throws UnsupportedKeyTypeException
     */
    private function hasPasswordResetRequested(IUser $user): bool {
        $lockedUsers = $this->getUsersWithPasswordResetRequest();

        foreach ($lockedUsers->keySet() as $key) {
            /** @var IUserState $userState */
            $userState = $lockedUsers->get($key);
            if ($user->getId() === $userState->getUser()->getId()) {
                return true;
            }
        }
        return false;

    }

    /**
     * @return HashTable
     * @throws UserStateException
     */
    public function getUsersWithPasswordResetRequest(): HashTable {
        return $this->getAll(IUserState::USER_STATE_REQUEST_PW_CHANGE);
    }

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotRemovedException
     */
    public function revertPasswordChangeRequest(IUser $user): void {
        if (false === $this->hasPasswordResetRequested($user)) {
            throw new UserStateException();
        }
        $this->remove($user, IUserState::USER_STATE_REQUEST_PW_CHANGE);
    }

}
