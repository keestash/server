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

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Exception;
use Keestash;
use Keestash\Core\DTO\User\User;
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;

/**
 * Class UserRepository
 *
 * @package Keestash\Core\Repository\User
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class UserRepository implements IUserRepository {

    private IDateTimeService $dateTimeService;
    private ILogger          $logger;
    private IBackend         $backend;

    public function __construct(
        IBackend           $backend
        , IDateTimeService $dateTimeService
        , ILogger          $logger
    ) {
        $this->backend         = $backend;
        $this->dateTimeService = $dateTimeService;
        $this->logger          = $logger;
    }

    /**
     * Returns an instance of IUser, if found in the database
     *
     * @param string $name The name of the user
     *
     * @return IUser|null
     * @throws KeestashException
     */
    public function getUser(string $name): ?IUser {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'u.id'
                , 'u.name'
                , 'u.password'
                , 'u.create_ts'
                , 'u.first_name'
                , 'u.last_name'
                , 'u.email'
                , 'u.phone'
                , 'u.website'
                , 'u.hash'
                , 'IF(us.state = \'delete.state.user\', true, false) AS deleted'
                , 'IF(us.state = \'lock.state.user\', true, false) AS locked'
            ]
        )
            ->from('user', 'u')
            ->leftJoin('u', 'user_state', 'us', 'u.id = us.user_id')
            ->where('u.name = ?')
            ->setParameter(0, $name);
        $result       = $queryBuilder->executeQuery();
        $users        = $result->fetchAllNumeric();
        $userCount    = count($users);

        $this->logger->debug("user count: $userCount");
        if (0 === $userCount) {
            return null;
        }

        if ($userCount > 1) {
            throw new KeestashException("found more then one user for the given name");
        }

        $row = $users[0];

        $user = new User();
        $user->setId((int) $row[0]);
        $user->setName($row[1]);
        $user->setPassword($row[2]);
        $user->setCreateTs(
            $this->dateTimeService->fromString($row[3])
        );
        $user->setFirstName($row[4]);
        $user->setLastName($row[5]);
        $user->setEmail($row[6]);
        $user->setPhone($row[7]);
        $user->setWebsite($row[8]);
        $user->setHash($row[9]);
        $user->setDeleted(
            true === (bool) $row[10]
        );
        $user->setLocked(
            true === (bool) $row[11]
        );

        return $user;
    }

    /**
     * Returns a list of users, registered for the app
     *
     * @return ArrayList
     * @throws Exception
     *
     */
    public function getAll(): ArrayList {
        $list = new ArrayList();

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'u.id'
                , 'u.name'
                , 'u.password'
                , 'u.create_ts'
                , 'u.first_name'
                , 'u.last_name'
                , 'u.email'
                , 'u.phone'
                , 'u.website'
                , 'u.hash'
                , 'IF(us.state = \'delete.state.user\', true, false) AS deleted'
                , 'IF(us.state = \'lock.state.user\', true, false) AS locked'
            ]
        )
            ->from('user', 'u')
            ->leftJoin('u', 'user_state', 'us', 'u.id = us.user_id');

        $result = $queryBuilder->executeQuery();
        $users  = $result->fetchAllAssociative();

        foreach ($users as $row) {

            $user = new User();
            $user->setId((int) $row['id']);
            $user->setName($row['name']);
            $user->setPassword($row['password']);
            $user->setCreateTs(
                $this->dateTimeService->fromString($row['create_ts'])
            );
            $user->setFirstName($row['first_name']);
            $user->setLastName($row['last_name']);
            $user->setEmail($row['email']);
            $user->setPhone($row['phone']);
            $user->setWebsite($row['website']);
            $user->setHash($row['hash']);
            $user->setDeleted((bool) $row['deleted']);
            $user->setLocked((bool) $row['locked']);

            $list->add($user);
        }

        return $list;
    }

    /**
     * Inserts an instance of IUser into the database
     *
     * @param IUser $user
     *
     * @return int|null
     *
     */
    public function insert(IUser $user): ?int {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('user')
            ->values(
                [
                    'first_name'  => '?'
                    , 'last_name' => '?'
                    , 'name'      => '?'
                    , 'email'     => '?'
                    , 'phone'     => '?'
                    , 'password'  => '?'
                    , 'website'   => '?'
                    , 'hash'      => '?'
                ]
            )
            ->setParameter(0, $user->getFirstName())
            ->setParameter(1, $user->getLastName())
            ->setParameter(2, $user->getName())
            ->setParameter(3, $user->getEmail())
            ->setParameter(4, $user->getPhone())
            ->setParameter(5, $user->getPassword())
            ->setParameter(6, $user->getWebsite())
            ->setParameter(7, $user->getHash())
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) return null;
        return (int) $lastInsertId;

    }

    /**
     * @param IUser $user
     *
     * @return bool
     *
     * TODO update roles and permissions
     */
    public function update(IUser $user): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder->update('user', 'u')
            ->set('u.first_name', '?')
            ->set('u.last_name', '?')
            ->set('u.name', '?')
            ->set('u.email', '?')
            ->set('u.phone', '?')
            ->set('u.password', '?')
            ->set('u.website', '?')
            ->set('u.hash', '?')
            ->where('u.id = ?')
            ->setParameter(0, $user->getFirstName())
            ->setParameter(1, $user->getLastName())
            ->setParameter(2, $user->getName())
            ->setParameter(3, $user->getEmail())
            ->setParameter(4, $user->getPhone())
            ->setParameter(5, $user->getPassword())
            ->setParameter(6, $user->getWebsite())
            ->setParameter(7, $user->getHash())
            ->setParameter(8, $user->getId())
            ->execute();

        return true;

    }

    /**
     * Returns an instance of IUser or null, if not found
     *
     * @param string $id
     *
     * @return IUser|null
     * @throws KeestashException
     */
    public function getUserById(string $id): ?IUser {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'u.id'
                , 'u.name'
                , 'u.password'
                , 'u.create_ts'
                , 'u.first_name'
                , 'u.last_name'
                , 'u.email'
                , 'u.phone'
                , 'u.website'
                , 'u.hash'
                , 'case when us.state = \'delete.state.user\' then true else false end AS deleted'
                , 'case when us.state = \'lock.state.user\' then true else false end AS locked'
            ]
        )
            ->from('user', 'u')
            ->leftJoin('u', 'user_state', 'us', 'u.id = us.user_id')
            ->where('u.id = ?')
            ->setParameter(0, $id);
        $users     = $queryBuilder->executeQuery()->fetchAllAssociative();
        $userCount = count($users);

        if (0 === $userCount) {
            return null;
        }

        if ($userCount > 1) {
            throw new KeestashException("found more then one user for the given name");
        }

        $row  = $users[0];
        $user = new User();
        $user->setId((int) $row['id']);
        $user->setName($row['name']);
        $user->setPassword($row['password']);
        $user->setCreateTs(
            $this->dateTimeService->fromString($row['create_ts'])
        );
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setEmail($row['email']);
        $user->setPhone($row['phone']);
        $user->setWebsite($row['website']);
        $user->setHash($row['hash']);
        $user->setDeleted(
            true === (bool) $row['deleted']
        );
        $user->setLocked(
            true === (bool) $row['locked']
        );

        return $user;
    }

    public function getUserByEmail(string $email): ?IUser {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'u.id'
                , 'u.name'
                , 'u.password'
                , 'u.create_ts'
                , 'u.first_name'
                , 'u.last_name'
                , 'u.email'
                , 'u.phone'
                , 'u.website'
                , 'u.hash'
                , 'case when us.state = \'delete.state.user\' then true else false end AS deleted'
                , 'case when us.state = \'lock.state.user\' then true else false end AS locked'
            ]
        )
            ->from('user', 'u')
            ->leftJoin('u', 'user_state', 'us', 'u.id = us.user_id')
            ->where('u.email = ?')
            ->setParameter(0, $email);
        $users     = $queryBuilder->executeQuery()->fetchAllAssociative();
        $userCount = count($users);

        if (0 === $userCount) {
            return null;
        }

        if ($userCount > 1) {
            throw new KeestashException("found more then one user for the given name");
        }

        $row  = $users[0];
        $user = new User();
        $user->setId((int) $row['id']);
        $user->setName($row['name']);
        $user->setPassword($row['password']);
        $user->setCreateTs(
            $this->dateTimeService->fromString($row['create_ts'])
        );
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setEmail($row['email']);
        $user->setPhone($row['phone']);
        $user->setWebsite($row['website']);
        $user->setHash($row['hash']);
        $user->setDeleted(
            true === (bool) $row['deleted']
        );
        $user->setLocked(
            true === (bool) $row['locked']
        );

        return $user;
    }

    /**
     * Returns an instance of IUser or null, if not found
     *
     * @param string $hash
     *
     * @return IUser|null
     * @throws KeestashException
     */
    public function getUserByHash(string $hash): ?IUser {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'u.id'
                , 'u.name'
                , 'u.password'
                , 'u.create_ts'
                , 'u.first_name'
                , 'u.last_name'
                , 'u.email'
                , 'u.phone'
                , 'u.website'
                , 'u.hash'
                , 'IF(us.state = \'delete.state.user\', true, false) AS deleted'
                , 'IF(us.state = \'lock.state.user\', true, false) AS locked'
            ]
        )
            ->from('user', 'u')
            ->leftJoin('u', 'user_state', 'us', 'u.id = us.user_id')
            ->where('u.hash = ?')
            ->setParameter(0, $hash);
        $users     = $queryBuilder->executeQuery()->fetchAllAssociative();
        $userCount = count($users);

        if (0 === $userCount) {
            return null;
        }

        if ($userCount > 1) {
            throw new KeestashException("found more then one user for the given name");
        }

        $row  = $users[0];
        $user = new User();
        $user->setId((int) $row['id']);
        $user->setName($row['name']);
        $user->setPassword($row['password']);
        $user->setCreateTs(
            $this->dateTimeService->fromString($row['create_ts'])
        );
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setEmail($row['email']);
        $user->setPhone($row['phone']);
        $user->setWebsite($row['website']);
        $user->setHash($row['hash']);
        $user->setDeleted(
            true === (bool) $row['deleted']
        );
        $user->setLocked(
            true === (bool) $row['locked']
        );

        return $user;
    }


    /**
     * Removes an instance of IUser
     *
     * @param IUser $user
     *
     * @return bool
     */
    public function remove(IUser $user): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('user')
                ->where('id = ?')
                ->setParameter(0, $user->getId())
                ->execute() !== 0;
    }

    public function searchUsers(string $name): ArrayList {
        $list = new ArrayList();

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'u.id'
                , 'u.name'
                , 'u.password'
                , 'u.create_ts'
                , 'u.first_name'
                , 'u.last_name'
                , 'u.email'
                , 'u.phone'
                , 'u.website'
                , 'u.hash'
                , 'CASE us.state WHEN \'delete.state.user\' THEN true ELSE false END AS deleted'
                , 'CASE us.state WHEN \'lock.state.user\' THEN true ELSE false END AS locked'
            ]
        )
            ->from('user', 'u')
            ->where('u.name like ?')
            ->leftJoin('u', 'user_state', 'us', 'u.id = us.user_id')
            ->setParameter(0, '%' . $name . '%');

        $result = $queryBuilder->executeQuery();
        $users  = $result->fetchAllAssociative();

        foreach ($users as $row) {

            $user = new User();
            $user->setId((int) $row['id']);
            $user->setName($row['name']);
            $user->setPassword($row['password']);
            $user->setCreateTs(
                $this->dateTimeService->fromString($row['create_ts'])
            );
            $user->setFirstName($row['first_name']);
            $user->setLastName($row['last_name']);
            $user->setEmail($row['email']);
            $user->setPhone($row['phone']);
            $user->setWebsite($row['website']);
            $user->setHash($row['hash']);
            $user->setDeleted((bool) $row['deleted']);
            $user->setLocked((bool) $row['locked']);

            $list->add($user);
        }

        return $list;
    }

}
