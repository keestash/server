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

namespace Keestash\Core\Repository\User;

use DateTime;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Core\DTO\User\UserState;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IJsonUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use PDO;

class UserStateRepository extends AbstractRepository implements IUserStateRepository {

    /** @var IUserRepository */
    private $userRepository;

    public function __construct(
        IBackend $backend
        , IUserRepository $userRepository
    ) {
        parent::__construct($backend);
        $this->userRepository = $userRepository;
    }

    public function unlock(IJsonUser $user): bool {
        if (false === $this->isLocked($user)) return true;
        return $this->remove($user, IUserState::USER_STATE_LOCK);
    }

    public function lock(IJsonUser $user): bool {
        if (true === $this->isLocked($user)) return true;
        return $this->insert($user, IUserState::USER_STATE_LOCK);
    }

    public function delete(IJsonUser $user): bool {
        if (true === $this->isDeleted($user)) return true;
        $locked  = $this->insert($user, IUserState::USER_STATE_LOCK);
        $deleted = $this->insert($user, IUserState::USER_STATE_DELETE);
        return true === $locked && true === $deleted;
    }

    public function revertDelete(IJsonUser $user): bool {
        if (false === $this->isDeleted($user)) return true;
        return $this->remove($user, IUserState::USER_STATE_DELETE);
    }

    public function getAll(?string $state = null): HashTable {
        $table = new HashTable();
        $sql   = "
                SELECT
                    us.`id`
                     , us.`user_id`
                     , us.`state`
                     , us.`valid_from`
                     , us.`create_ts`
                FROM `user_state` us";

        if (true === UserState::isValidState((string) $state)) {
            $sql = $sql . " WHERE us.`state` = :state";
        }

        $statement = parent::prepareStatement($sql);
        if (null === $statement) return $table;

        if (true === UserState::isValidState((string) $state)) {
            $statement->bindParam("state", $state);
        }

        $executed = $statement->execute();
        if (!$executed) return $table;
        if ($statement->rowCount() === 0) return $table;

        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id        = $row[0];
            $userId    = $row[1];
            $state     = $row[2];
            $validFrom = $row[3];
            $createTs  = $row[4];

            $userState = new UserState();
            $userState->setId((int) $id);
            $userState->setUser(
                $this->userRepository->getUserById((string) $userId)
            );
            $userState->setValidFrom(
                DateTimeUtil::fromMysqlDateTime($validFrom)
            );
            $userState->setCreateTs(
                DateTimeUtil::fromMysqlDateTime($createTs)
            );
            $userState->setState($state);

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
    private function insert(IJsonUser $user, string $state): bool {
        $sql = "insert into `user_state` (
                  `user_id`
                  , `state`
                  , `valid_from`
                  , `create_ts`
                  )
                  values (
                          :user_id
                          , :state
                          , :valid_from
                          , :create_ts
                          );";

        $statement = parent::prepareStatement($sql);

        $userId    = $user->getId();
        $validFrom = new DateTime();
        $validFrom = DateTimeUtil::formatMysqlDateTime(
            $validFrom
        );
        $createTs  = new DateTime();
        $createTs  = DateTimeUtil::formatMysqlDateTime(
            $createTs
        );

        $statement->bindParam("user_id", $userId);
        $statement->bindParam("state", $state);
        $statement->bindParam("valid_from", $validFrom);
        $statement->bindParam("create_ts", $createTs);

        if (false === $statement->execute()) return false;

        $lastInsertId = parent::getLastInsertId();

        if (null === $lastInsertId) return false;

        return true;
    }

    public function remove(IJsonUser $user, string $state): bool {
        $sql       = "DELETE FROM 
                            `user_state`
                      WHERE 
                            `user_id` = :user_id
                        AND `state` = :state
                            ;";
        $statement = $this->prepareStatement($sql);

        if (null === $statement) return false;
        $userId = $user->getId();
        $statement->bindParam("user_id", $userId);
        $statement->bindParam("state", $state);
        $statement->execute();
        $executed = false === $this->hasErrors($statement->errorCode());

        if (false === $executed) return false;
        return $executed;
    }


    public function removeAll(IJsonUser $user): bool {
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

    public function isLocked(IJsonUser $user): bool {
        $lockedUsers = $this->getLockedUsers();

        /** @var IUserState $userState */
        foreach ($lockedUsers->keySet() as $key) {
            $userState = $lockedUsers->get($key);
            if ($user->getId() === $userState->getUser()->getId()) return true;
        }
        return false;
    }

    public function isDeleted(IJsonUser $user): bool {
        $deletedUsers = $this->getDeletedUsers();

        /** @var IUserState $userState */
        foreach ($deletedUsers->keySet() as $key) {
            $userState = $deletedUsers->get($key);
            if ($user->getId() === $userState->getUser()->getId()) return true;
        }
        return false;
    }

}
