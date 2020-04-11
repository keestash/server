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
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Core\DTO\User\UserState;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
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

    public function unlock(IUser $user): bool {
        return $this->remove($user, IUserState::USER_STATE_LOCK);
    }

    public function lock(IUser $user): bool {
        return $this->insert($user, IUserState::USER_STATE_LOCK);
    }

    public function delete(IUser $user): bool {
        return $this->insert($user, IUserState::USER_STATE_DELETE);
    }

    public function revertDelete(IUser $user): bool {
        return $this->remove($user, IUserState::USER_STATE_DELETE);
    }

    public function getDeletedUsers(): ArrayList {
        $list = new ArrayList();
        $sql  = "
                SELECT
                    us.`id`
                     , us.`user_id`
                     , us.`state`
                     , us.`valid_from`
                     , us.`create_ts`
                FROM `user_state` us
                WHERE us.`state` = :state
                ;";

        $statement = parent::prepareStatement($sql);
        if (null === $statement) return $list;
        $state = IUserState::USER_STATE_DELETE;
        $statement->bindParam("state", $state);

        $executed = $statement->execute();
        if (!$executed) return $list;
        if ($statement->rowCount() === 0) return $list;

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

            $list->add($userState);
        }

        return $list;
    }


    private function insert(IUser $user, string $state): bool {
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

    public function remove(IUser $user, string $state): bool {
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
        $statement->bindParam("state", $statement);
        $statement->execute();

        return false === $this->hasErrors($statement->errorCode());
    }


}
