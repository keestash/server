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

use DateTime;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\User\UserState;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;

class UserStateRepository extends AbstractRepository implements IUserStateRepository {

    /** @var IUserRepository */
    private $userRepository;

    /** @var IDateTimeService */
    private $dateTimeService;

    public function __construct(
        IBackend $backend
        , IUserRepository $userRepository
        , IDateTimeService $dateTimeService
    ) {
        parent::__construct($backend);
        $this->userRepository  = $userRepository;
        $this->dateTimeService = $dateTimeService;
    }

    public function unlock(IUser $user): bool {
        if (false === $this->isLocked($user)) return true;
        return $this->remove($user, IUserState::USER_STATE_LOCK);
    }

    public function lock(IUser $user): bool {
        if (true === $this->isLocked($user)) return true;
        return $this->insert(
            $user
            , IUserState::USER_STATE_LOCK
            , null
        );
    }

    public function delete(IUser $user): bool {
        if (true === $this->isDeleted($user)) return true;
        $locked  = $this->insert(
            $user
            , IUserState::USER_STATE_LOCK
            , null
        );
        $deleted = $this->insert(
            $user
            , IUserState::USER_STATE_DELETE
            , null
        );
        return true === $locked && true === $deleted;
    }

    public function revertDelete(IUser $user): bool {
        if (false === $this->isDeleted($user)) return true;
        return $this->remove($user, IUserState::USER_STATE_DELETE);
    }

    public function getAll(?string $state = null): HashTable {
        $table = new HashTable();

        $queryBuilder = $this->getQueryBuilder();
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

        $usersStates = $queryBuilder->execute()->fetchAll();

        foreach ($usersStates as $row) {
            $id            = $row["id"];
            $userId        = $row["user_id"];
            $state         = $row["state"];
            $validFrom     = $row["valid_from"];
            $createTs      = $row["create_ts"];
            $userStateHash = $row["state_hash"];

            $userState = new UserState();
            $userState->setId((int) $id);
            $userState->setUser(
                $this->userRepository->getUserById((string) $userId)
            );
            $userState->setValidFrom(
                $this->dateTimeService->fromString($validFrom)
            );
            $userState->setCreateTs(
                $this->dateTimeService->fromString($createTs)
            );
            $userState->setState($state);
            $userState->setStateHash($userStateHash);

            $table->put($userState->getId(), $userState);
        }

        return $table;
    }

    public function getDeletedUsers(): HashTable {
        return $this->getAll(IUserState::USER_STATE_DELETE);
    }

    public function getLockedUsers(): HashTable {
        return $this->getAll(IUserState::USER_STATE_LOCK);
    }

    // TODO check whether already exists
    private function insert(IUser $user, string $state, ?string $hash = null): bool {

        $queryBuilder = $this->getQueryBuilder();
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
            ->setParameter(3,
                $this->dateTimeService->toYMDHIS(new DateTime())
            )
            ->setParameter(4,
                $this->dateTimeService->toYMDHIS(new DateTime())
            )
            ->execute();

        $lastInsertId = $this->getDoctrineLastInsertId();

        if (null === $lastInsertId) return false;
        return true;

    }

    public function remove(IUser $user, string $state): bool {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->delete('user_state')
                ->where('user_id = ?')
                ->andWhere('state = ?')
                ->setParameter(0, $user->getId())
                ->setParameter(1, $state)
                ->execute() !== 0;
    }


    public function removeAll(IUser $user): bool {
        $lockRemoved   = $this->remove(
            $user
            , IUserState::USER_STATE_LOCK
        );
        $deleteRemoved = $this->remove(
            $user
            , IUserState::USER_STATE_DELETE
        );
        return true === $lockRemoved && true === $deleteRemoved;
    }

    public function isLocked(IUser $user): bool {
        $lockedUsers = $this->getLockedUsers();

        /** @var IUserState $userState */
        foreach ($lockedUsers->keySet() as $key) {
            $userState = $lockedUsers->get($key);
            if ($user->getId() === $userState->getUser()->getId()) return true;
        }
        return false;
    }

    public function isDeleted(IUser $user): bool {
        $deletedUsers = $this->getDeletedUsers();

        /** @var IUserState $userState */
        foreach ($deletedUsers->keySet() as $key) {
            $userState = $deletedUsers->get($key);
            if ($user->getId() === $userState->getUser()->getId()) return true;
        }
        return false;
    }

    public function requestPasswordReset(IUser $user, string $hash): bool {
        if (true === $this->hasPasswordResetRequested($user)) return true;
        return $this->insert(
            $user
            , IUserState::USER_STATE_REQUEST_PW_CHANGE
            , $hash
        );

    }

    public function revertPasswordChangeRequest(IUser $user): bool {
        if (false === $this->hasPasswordResetRequested($user)) return true;
        return $this->remove($user, IUserState::USER_STATE_REQUEST_PW_CHANGE);
    }

    public function hasPasswordResetRequested(IUser $user): bool {
        $lockedUsers = $this->getUsersWithPasswordResetRequest();

        /** @var IUserState $userState */
        foreach ($lockedUsers->keySet() as $key) {
            $userState = $lockedUsers->get($key);
            if ($user->getId() === $userState->getUser()->getId()) {
                return true;
            }
        }
        return false;

    }

    public function getUsersWithPasswordResetRequest(): HashTable {
        return $this->getAll(IUserState::USER_STATE_REQUEST_PW_CHANGE);
    }

}
