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

namespace Keestash\Core\Repository\Token;

use Keestash;
use Keestash\Core\DTO\Token;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\IJsonToken;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use PDO;

class TokenRepository extends AbstractRepository implements ITokenRepository {

    private $userRepository = null;

    public function __construct(
        IBackend $backend
        , IUserRepository $userRepository
    ) {
        parent::__construct($backend);
        $this->userRepository = $userRepository;
    }

    public function get(int $id): ?IJsonToken {
        $sql = "
        select
                `id`
                , `name`
                , `value`
                , `user_id`
                , `create_ts`
           from `token`
                where `id` = :id;
        ";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) return null;
        $statement->bindParam("id", $id);
        $executed = $statement->execute();
        if (!$executed) return null;
        if ($statement->rowCount() === 0) return null;

        $token = null;
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id       = $row[0];
            $name     = $row[1];
            $value    = $row[2];
            $userId   = $row[3];
            $createTs = $row[4];

            $token = new Token();
            $token->setId((int) $id);
            $token->setName($name);
            $token->setValue($value);
            $token->setUser(
                $this->userRepository->getUserById($userId)
            );
            $token->setCreateTs((int) $createTs);
        }
        return $token;
    }

    public function getByHash(string $hash): ?IJsonToken {
        $sql = "
        select
                `id`
                , `name`
                , `value`
                , `user_id`
                , `create_ts`
           from `token`
                where `value` = :hash;
        ";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) return null;
        $statement->bindParam("hash", $hash);
        $executed = $statement->execute();
        if (!$executed) return null;
        if ($statement->rowCount() === 0) return null;

        $token = null;
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id       = $row[0];
            $name     = $row[1];
            $value    = $row[2];
            $userId   = $row[3];
            $createTs = $row[4];

            $token = new Token();
            $token->setId((int) $id);
            $token->setName($name);
            $token->setValue($value);
            $token->setUser(
                $this->userRepository->getUserById((string) $userId)
            );
            $token->setCreateTs((int) $createTs);
        }
        return $token;
    }

    public function add(IJsonToken $token): ?int {
        $sql = "insert into `token` (
                  `name`
                  , `value`
                  , `user_id`
                  , `create_ts`
                  )
                  values (
                          :name
                          , :value
                          , :user_id
                          , :create_ts
                          );";

        $statement = parent::prepareStatement($sql);

        $name     = $token->getName();
        $value    = $token->getValue();
        $userId   = $token->getUser()->getId();
        $createTs = $token->getCreateTs()->getTimestamp();

        $statement->bindParam("name", $name);
        $statement->bindParam("value", $value);
        $statement->bindParam("user_id", $userId);
        $statement->bindParam("create_ts", $createTs);

        if (false === $statement->execute()) return null;

        $lastInsertId = (int) parent::getLastInsertId();

        if (0 === $lastInsertId) return null;
        return $lastInsertId;

    }

}
