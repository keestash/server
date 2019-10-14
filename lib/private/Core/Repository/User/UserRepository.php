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
use Keestash\Core\DTO\User;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\IUser;
use KSP\Core\Repository\Permission\IRoleRepository;
use KSP\Core\Repository\User\IUserRepository;
use PDO;
use PDOException;

class UserRepository extends AbstractRepository implements IUserRepository {

    /** @var null|IRoleRepository $roleManager */
    private $roleManager = null;

    public function __construct(
        IBackend $backend
        , IRoleRepository $roleManager
    ) {
        parent::__construct($backend);
        $this->roleManager = $roleManager;
    }

    public function getUser(string $name): ?IUser {
        $sql = "select 
                      u.`id`
                      , u.`name`
                      , u.`password`
                      , u.`create_ts`
                      , u.`first_name`
                      , u.`last_name`
                      , u.`email`
                      , u.`phone`
                      , u.`website`
                from `user` u 
                  where `name` = :name;";

        $statement = parent::prepareStatement($sql);
        if (null === $statement) return null;
        $statement->bindParam("name", $name);
        $executed = $statement->execute();
        if (!$executed) return null;
        if ($statement->rowCount() === 0) return null;

        $user = null;
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id        = $row[0];
            $name      = $row[1];
            $password  = $row[2];
            $createTs  = $row[3];
            $firstName = $row[4];
            $lastName  = $row[5];
            $email     = $row[6];
            $phone     = $row[7];
            $website   = $row[8];

            $user = new User();
            $user->setId($id);
            $user->setName($name);
            $user->setPassword($password);
            $user->setCreateTs((int) $createTs);
            $user->setFirstName($firstName);
            $user->seKSAstName($lastName);
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setWebsite($website);
            $user->setLastLogin(new DateTime()); // TODO implement
            $roles = $this->roleManager->getRolesByUser($user);
            $user->setRoles($roles);
        }

        return $user;
    }

    public function getUserByMail(string $email): ?IUser {
        $sql       = "select 
                      `id`
                      , `name`
                      , `password`
                      , `create_ts`
                      , `first_name`
                      , `last_name`
                      , `email`
                      , `phone`
                      , `website`
                from `user` u 
                  where `email` = :email;";
        $statement = parent::prepareStatement($sql);
        if (null === $statement) return null;
        $statement->bindParam("email", $email);
        $executed = $statement->execute();
        if (!$executed) return null;
        if ($statement->rowCount() === 0) return null;

        $user = null;
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id        = $row[0];
            $name      = $row[1];
            $password  = $row[2];
            $createTs  = $row[3];
            $firstName = $row[4];
            $lastName  = $row[5];
            $email     = $row[6];
            $phone     = $row[7];
            $website   = $row[8];

            $user = new User();
            $user->setId($id);
            $user->setName($name);
            $user->setPassword($password);
            $user->setCreateTs($createTs);
            $user->setFirstName($firstName);
            $user->seKSAstName($lastName);
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setWebsite($website);
            $user->setLastLogin(new DateTime()); // TODO implement
            $roles = $this->roleManager->getRolesByUser($user);
            $user->setRoles($roles);
        }

        return $user;
    }

    public function getAll(): ?ArrayList {
        $list      = new ArrayList();
        $sql       = "select 
                      u.`id`
                      , u.`name`
                      , u.`password`
                      , u.`create_ts`
                      , u.`first_name`
                      , u.`last_name`
                      , u.`email`
                      , u.`phone`
                      , u.`email`
                from `user` u;"; //TODO add email
        $statement = parent::prepareStatement($sql);
        if (null === $statement) return null;
        $executed = $statement->execute();
        if (!$executed) return null;
        if ($statement->rowCount() === 0) return null;

        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id        = $row[0];
            $name      = $row[1];
            $password  = $row[2];
            $createTs  = $row[3];
            $firstName = $row[4];
            $lastName  = $row[5];
            $email     = $row[6];
            $phone     = $row[7];
            $website   = $row[8];

            $user = new User();
            $user->setId((int) $id);
            $user->setName($name);
            $user->setPassword($password);
            $user->setCreateTs((int) $createTs);
            $user->setFirstName($firstName);
            $user->seKSAstName($lastName);
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setWebsite($website);
            $user->setLastLogin(new DateTime()); // TODO implement
            $roles = $this->roleManager->getRolesByUser($user);
            $user->setRoles($roles);

            $list->add($user);
        }

        return $list;
    }

    /**
     * @param IUser $user
     * @return int|null
     *
     * TODO insert roles and permissions
     */
    public function insert(IUser $user): ?int {
        $sql = "insert into user (
                  `first_name`
                  , `last_name`
                  , `name`
                  , `email`
                  , `phone`
                  , `password`
                  , `website`
                  )
                  values (
                          :first_name
                          , :last_name
                          , :name
                          , :email
                          , :phone
                          , :password
                          , :website
                          );";

        $statement = parent::prepareStatement($sql);

        $firstName = $user->getFirstName();
        $lastName  = $user->geKSAstName();
        $name      = $user->getName();
        $email     = $user->getEmail();
        $phone     = $user->getPhone();
        $password  = $user->getPassword();
        $website   = $user->getWebsite();

        $statement->bindParam("first_name", $firstName);
        $statement->bindParam("last_name", $lastName);
        $statement->bindParam("name", $name);
        $statement->bindParam("email", $email);
        $statement->bindParam("phone", $phone);
        $statement->bindParam("password", $password);
        $statement->bindParam("website", $website);

        if (false === $statement->execute()) return null;

        $lastInsertId = (int) parent::getLastInsertId();

        if (0 === $lastInsertId) return null;
        return $lastInsertId;

    }

    /**
     * @param IUser $user
     * @return bool
     *
     * TODO insert roles and permissions
     */
    public function update(IUser $user): bool {
        try {
            $sql = "
                update `user`
                    set `first_name` = :first_name
                      , `last_name`  = :last_name
                      , `name`       = :name
                      , `email`      = :email
                      , `phone`      = :phone
                      , `password`   = :password
                      , `website`    = :website
                    where `id` = :id;
        ";

            $statement = parent::prepareStatement($sql);

            if (null === $statement || false === $statement) {
                return false;
            }

            $firstName = $user->getFirstName();
            $lastName  = $user->geKSAstName();
            $name      = $user->getName();
            $email     = $user->getEmail();
            $phone     = $user->getPhone();
            $password  = $user->getPassword();
            $id        = $user->getId();
            $website   = $user->getWebsite();

            $statement->bindParam(":id", $id);
            $statement->bindParam(":first_name", $firstName);
            $statement->bindParam(":last_name", $lastName);
            $statement->bindParam(":name", $name);
            $statement->bindParam(":email", $email);
            $statement->bindParam(":phone", $phone);
            $statement->bindParam(":password", $password);
            $statement->bindParam(":website", $website);

            $executed = $statement->execute();
        } catch (PDOException $e) {
            FileLogger::error($e->getMessage());
            throw $e;
        }
        return $executed;

    }

    public function exists(string $id): bool {
        return null !== $this->getUserById($id);
    }

    public function getUserById(string $id): ?IUser {
        $sql       = "select 
                      `id`
                      , `name`
                      , `password`
                      , `create_ts`
                      , `first_name`
                      , `last_name`
                      , `email`
                      , `phone`
                      , `website`
                from `user` u 
                  where `id` = :id;;";
        $statement = parent::prepareStatement($sql);
        if (null === $statement) return null;
        $statement->bindParam("id", $id);
        $executed = $statement->execute();
        if (!$executed) return null;
        if ($statement->rowCount() === 0) return null;

        $user = null;
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id        = $row[0];
            $name      = $row[1];
            $password  = $row[2];
            $createTs  = $row[3];
            $firstName = $row[4];
            $lastName  = $row[5];
            $email     = $row[6];
            $phone     = $row[7];
            $website   = $row[8];

            $user = new User();
            $user->setId($id);
            $user->setName($name);
            $user->setPassword($password);
            $user->setCreateTs((int) $createTs);
            $user->setFirstName($firstName);
            $user->seKSAstName($lastName);
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setWebsite($website);
            $user->setLastLogin(new DateTime()); // TODO implement
            $roles = $this->roleManager->getRolesByUser($user);
            $user->setRoles($roles);
        }

        return $user;
    }

    public function nameExists(string $name): bool {
        return null !== $this->getUserByName($name);
    }

    public function getUserByName(string $name): ?IUser {
        $sql       = "select 
                      `id`
                      , `name`
                      , `password`
                      , `create_ts`
                      , `first_name`
                      , `last_name`
                      , `email`
                      , `phone`
                      , `website`
                from `user` u 
                  where `name` = :name;";
        $statement = parent::prepareStatement($sql);
        if (null === $statement) return null;
        $statement->bindParam("name", $name);
        $executed = $statement->execute();
        if (!$executed) return null;
        if ($statement->rowCount() === 0) return null;

        $user = null;
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id        = $row[0];
            $name      = $row[1];
            $password  = $row[2];
            $createTs  = $row[3];
            $firstName = $row[4];
            $lastName  = $row[5];
            $email     = $row[6];
            $phone     = $row[7];
            $website   = $row[8];

            $user = new User();
            $user->setId($id);
            $user->setName($name);
            $user->setPassword($password);
            $user->setCreateTs((int) $createTs);
            $user->setFirstName($firstName);
            $user->seKSAstName($lastName);
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setWebsite($website);
            $user->setLastLogin(new DateTime()); // TODO implement
            $roles = $this->roleManager->getRolesByUser($user);
            $user->setRoles($roles);
        }

        return $user;
    }

}