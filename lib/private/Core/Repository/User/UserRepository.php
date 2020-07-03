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
use Doctrine\DBAL\FetchMode;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPUtil\Log\FileLogger;
use Exception;
use Keestash;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Repository\AbstractRepository;
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Permission\IRoleRepository;
use KSP\Core\Repository\User\IUserRepository;

/**
 * Class UserRepository
 *
 * @package Keestash\Core\Repository\User
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class UserRepository extends AbstractRepository implements IUserRepository {

    /** @var null|IRoleRepository $roleManager */
    private $roleManager;

    /** @var IDateTimeService */
    private $dateTimeService;

    public function __construct(
        IBackend $backend
        , IRoleRepository $roleManager
        , IDateTimeService $dateTimeService
    ) {
        parent::__construct($backend);
        $this->roleManager     = $roleManager;
        $this->dateTimeService = $dateTimeService;
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
        $queryBuilder = $this->getQueryBuilder();
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
            ->where('u.name = ?')
            ->setParameter(0, $name);
        $users     = $queryBuilder->execute()->fetchAll();
        $userCount = count($users);

        if (0 === $userCount) {
            return null;
        }

        if ($userCount > 1) {
            throw new KeestashException("found more then one user for the given name");
        }

        $row = $users[0];

        $user = new User();
        $user->setId((int) $row['id']);
        $user->setName($row['name']);
        $user->setPassword($row['password']);
        $user->setCreateTs(
            $this->dateTimeService->toDateTime((int) $row['create_ts'])
        );
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setEmail($row['email']);
        $user->setPhone($row['phone']);
        $user->setWebsite($row['website']);
        $user->setHash($row['hash']);
        $user->setLastLogin(new DateTime()); // TODO implement
        $user->setDeleted(
            true === (bool) $row['deleted']
        );
        $user->setLocked(
            true === (bool) $row['locked']
        );
        $user->setRoles(
            $this->roleManager->getRolesByUser($user)
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

        $queryBuilder = $this->getQueryBuilder();
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

        $statement = $queryBuilder->execute();
        $users     = $statement->fetchAll(FetchMode::ASSOCIATIVE);

        foreach ($users as $row) {

            $user = new User();
            $user->setId((int) $row['id']);
            $user->setName($row['name']);
            $user->setPassword($row['password']);
            $user->setCreateTs(
                $this->dateTimeService->toDateTime((int) $row['create_ts'])
            );
            $user->setFirstName($row['first_name']);
            $user->setLastName($row['last_name']);
            $user->setEmail($row['email']);
            $user->setPhone($row['phone']);
            $user->setWebsite($row['website']);
            $user->setLastLogin(new DateTime()); // TODO implement
            $user->setHash($row['hash']);
            $user->setDeleted((bool) $row['deleted']);
            $user->setLocked((bool) $row['locked']);
            $user->setRoles(
                $this->roleManager->getRolesByUser($user)
            );

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
     * TODO insert roles and permissions
     */
    public function insert(IUser $user): ?int {
        $queryBuilder = $this->getQueryBuilder();
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
            ->execute();

        $lastInsertId = $this->getDoctrineLastInsertId();

        if (null === $lastInsertId) return null;
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
        $queryBuilder = $this->getQueryBuilder();

        $q = $queryBuilder->update('user', 'u')
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
;
        FileLogger::debug(json_encode($queryBuilder->getSQL()));

//        exit();
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
        $queryBuilder = $this->getQueryBuilder();
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
            ->where('u.id = ?')
            ->setParameter(0, $id);
        $users     = $queryBuilder->execute()->fetchAll();
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
            $this->dateTimeService->toDateTime((int) $row['create_ts'])
        );
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setEmail($row['email']);
        $user->setPhone($row['phone']);
        $user->setWebsite($row['website']);
        $user->setHash($row['hash']);
        $user->setLastLogin(new DateTime()); // TODO implement
        $user->setDeleted(
            true === (bool) $row['deleted']
        );
        $user->setLocked(
            true === (bool) $row['locked']
        );
        $user->setRoles(
            $this->roleManager->getRolesByUser($user)
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
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->delete('user')
                ->where('id = ?')
                ->setParameter(0, $user->getId())
                ->execute()
                ->columnCount() !== 0;
    }

}
