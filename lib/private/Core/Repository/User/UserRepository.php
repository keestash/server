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

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Core\DTO\User\NullUser;
use Keestash\Core\DTO\User\User;
use Keestash\Exception\Repository\TooManyRowsException;
use Keestash\Exception\User\UserException;
use Keestash\Exception\User\UserNotCreatedException;
use Keestash\Exception\User\UserNotDeletedException;
use Keestash\Exception\User\UserNotFoundException;
use Keestash\Exception\User\UserNotUpdatedException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\LDAP\ILDAPUserRepository;
use KSP\Core\Repository\User\IUserRepository;
use Override;
use Psr\Log\LoggerInterface;

/**
 * Class UserRepository
 *
 * @package Keestash\Core\Repository\User
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
final readonly class UserRepository implements IUserRepository {

    public function __construct(
        private IBackend                $backend,
        private IDateTimeService        $dateTimeService,
        private LoggerInterface         $logger,
        private RBACRepositoryInterface $rbacRepository,
        private ILDAPUserRepository     $ldapUserRepository
    ) {
    }

    /**
     * Returns an instance of IUser, if found in the database
     *
     * @param string $name The name of the user
     *
     * @return IUser
     * @throws UserException
     */
    #[Override]
    public function getUser(string $name): IUser {
        try {
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
                    , 'u.locale'
                    , 'u.language'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'lock.state.user\') THEN true ELSE false END AS locked'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'delete.state.user\') THEN true ELSE false END AS deleted'
                ]
            )
                ->from('user', 'u')
                ->where('u.name = ?')
                ->setParameter(0, $name);
            $result       = $queryBuilder->executeQuery();
            $users        = $result->fetchAllNumeric();
            $userCount    = count($users);

            if (0 === $userCount) {
                return new NullUser();
            }

            if ($userCount > 1) {
                throw new TooManyRowsException("found more then one user for the given name");
            }

            $row  = $users[0];
            $user = new User();
            $user->setId((int) $row[0]);
            $user->setName((string) $row[1]);
            $user->setPassword((string) $row[2]);
            $user->setCreateTs(
                $this->dateTimeService->fromString((string) $row[3])
            );
            $user->setFirstName((string) $row[4]);
            $user->setLastName((string) $row[5]);
            $user->setEmail((string) $row[6]);
            $user->setPhone((string) $row[7]);
            $user->setWebsite((string) $row[8]);
            $user->setHash((string) $row[9]);
            $user->setLocale((string) $row[10]);
            $user->setLanguage((string) $row[11]);
            $user->setLocked(
                1 === (int) $row[12]
            );
            $user->setDeleted(
                1 === (int) $row[13]
            );
            $user->setRoles(
                $this->rbacRepository->getRolesByUser($user)
            );
            $user->setLdapUser(
                $this->ldapUserRepository->isLDAPUser($user)
            );
        } catch (Exception $exception) {
            $message = 'error while getting user';
            $this->logger->error(
                $message
                , ['exception' => $exception]
            );
            throw new UserException(message: $message, previous: $exception);
        } catch (TooManyRowsException $exception) {
            $message = 'too many users found';
            $this->logger->error(
                $message
                , [
                    'exception'  => $exception
                    , 'userName' => $name
                ]
            );
            throw new UserException(message: $message, previous: $exception);
        }
        return $user;
    }

    /**
     * Returns a list of users, registered for the app
     *
     * @return ArrayList
     * @throws UserException
     */
    #[Override]
    public function getAll(): ArrayList {
        try {
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
                    , 'u.locale'
                    , 'u.language'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'delete.state.user\') THEN true ELSE false END AS deleted'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'lock.state.user\') THEN true ELSE false END AS locked'
                ]
            )
                ->from('user', 'u');

            $result = $queryBuilder->executeQuery();
            $users  = $result->fetchAllAssociative();

            foreach ($users as $row) {

                $user = new User();
                $user->setId((int) $row['id']);
                $user->setName((string) $row['name']);
                $user->setPassword((string) $row['password']);
                $user->setCreateTs(
                    $this->dateTimeService->fromString((string) $row['create_ts'])
                );
                $user->setFirstName((string) $row['first_name']);
                $user->setLastName((string) $row['last_name']);
                $user->setEmail((string) $row['email']);
                $user->setPhone((string) $row['phone']);
                $user->setWebsite((string) $row['website']);
                $user->setHash((string) $row['hash']);
                $user->setDeleted(
                    1 === (int) $row['deleted']
                );
                $user->setLocked(
                    1 === (int) $row['locked']
                );
                $user->setLocale((string) $row['locale']);
                $user->setLanguage((string) $row['language']);
                $user->setRoles(
                    $this->rbacRepository->getRolesByUser($user)
                );
                $user->setLdapUser(
                    $this->ldapUserRepository->isLDAPUser($user)
                );

                $list->add($user);
            }

            return $list;
        } catch (Exception $exception) {
            $this->logger->error('error retrieving all users', ['exception' => $exception]);
            throw new UserException();
        }
    }

    /**
     * Inserts an instance of IUser into the database
     *
     * @param IUser $user
     *
     * @return IUser
     * @throws UserNotCreatedException
     */
    #[Override]
    public function insert(IUser $user): IUser {
        try {
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
                        , 'locale'    => '?'
                        , 'language'  => '?'
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
                ->setParameter(8, $user->getLocale())
                ->setParameter(9, $user->getLanguage())
                ->executeStatement();

            $lastInsertId = $this->backend->getConnection()->lastInsertId();

            if (false === is_numeric($lastInsertId)) {
                $this->logger->error('error with creating user', ['lastInsertId' => $lastInsertId, 'sql' => $queryBuilder->getSQL()]);
                throw new UserNotCreatedException();
            }

            $user->setId((int) $lastInsertId);
            return $user;

        } catch (Exception $exception) {
            $this->logger->error('error while creating user', ['exception' => $exception]);
            throw new UserNotCreatedException();
        }
    }

    /**
     * @param IUser $user
     * @return IUser
     * @throws UserNotUpdatedException
     *
     * TODO update roles and permissions
     */
    #[Override]
    public function update(IUser $user): IUser {

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        try {
            $queryBuilder->update('user')
                ->set('first_name', '?')
                ->set('last_name', '?')
                ->set('name', '?')
                ->set('email', '?')
                ->set('phone', '?')
                ->set('password', '?')
                ->set('website', '?')
                ->set('hash', '?')
                ->set('locale', '?')
                ->set('language', '?')
                ->where('id = ?')
                ->setParameter(0, $user->getFirstName())
                ->setParameter(1, $user->getLastName())
                ->setParameter(2, $user->getName())
                ->setParameter(3, $user->getEmail())
                ->setParameter(4, $user->getPhone())
                ->setParameter(5, $user->getPassword())
                ->setParameter(6, $user->getWebsite())
                ->setParameter(7, $user->getHash())
                ->setParameter(8, $user->getLocale())
                ->setParameter(9, $user->getLanguage())
                ->setParameter(10, $user->getId())
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error(
                'error while updating user'
                , [
                    'exception' => $exception->getMessage()
                    , 'sql'     => $queryBuilder->getSQL()
                ]
            );
            throw new UserNotUpdatedException();
        }

        return $user;

    }

    /**
     * Returns an instance of IUser or null, if not found
     *
     * @param string $id
     *
     * @return IUser
     * @throws UserNotFoundException
     */
    #[Override]
    public function getUserById(string $id): IUser {
        try {
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
                    , 'u.locale'
                    , 'u.language'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'lock.state.user\') THEN true ELSE false END AS locked'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'delete.state.user\') THEN true ELSE false END AS deleted'
                ]
            )
                ->from('user', 'u')
                ->where('u.id = ?')
                ->setParameter(0, $id);
            $users     = $queryBuilder->executeQuery()->fetchAllAssociative();
            $userCount = count($users);

            if (0 === $userCount) {
                $this->logger->debug('user not found', ['usercount' => $userCount, 'id' => $id]);
                throw new UserNotFoundException();
            }

            if ($userCount > 1) {
                throw new TooManyRowsException("found more then one user for the given id");
            }

            $row  = $users[0];
            $user = new User();
            $user->setId((int) $row['id']);
            $user->setName((string) $row['name']);
            $user->setPassword((string) $row['password']);
            $user->setCreateTs(
                $this->dateTimeService->fromString((string) $row['create_ts'])
            );
            $user->setFirstName((string) $row['first_name']);
            $user->setLastName((string) $row['last_name']);
            $user->setEmail((string) $row['email']);
            $user->setPhone((string) $row['phone']);
            $user->setWebsite((string) $row['website']);
            $user->setHash((string) $row['hash']);
            $user->setLocale((string) $row['locale']);
            $user->setLanguage((string) $row['language']);
            $user->setDeleted(
                1 === (int) $row['deleted']
            );
            $user->setLocked(
                1 === (int) $row['locked']
            );
            $user->setRoles(
                $this->rbacRepository->getRolesByUser($user)
            );
            $user->setLdapUser(
                $this->ldapUserRepository->isLDAPUser($user)
            );

            return $user;
        } catch (Exception|TooManyRowsException $exception) {
            $this->logger->error('error while retrieving user', ['exception' => $exception, 'id' => $id]);
            throw new UserNotFoundException();
        }
    }

    /**
     * @param string $email
     * @return IUser
     * @throws UserNotFoundException
     */
    #[Override]
    public function getUserByEmail(string $email): IUser {
        try {
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
                    , 'u.locale'
                    , 'u.language'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'lock.state.user\') THEN true ELSE false END AS locked'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'delete.state.user\') THEN true ELSE false END AS deleted'
                ]
            )
                ->from('user', 'u')
                ->where('u.email = ?')
                ->setParameter(0, $email);
            $users     = $queryBuilder->executeQuery()->fetchAllAssociative();
            $userCount = count($users);

            if (0 === $userCount) {
                return new NullUser();
            }

            if ($userCount > 1) {
                throw new TooManyRowsException("found more then one user for the given name");
            }

            $row  = $users[0];
            $user = new User();
            $user->setId((int) $row['id']);
            $user->setName((string) $row['name']);
            $user->setPassword((string) $row['password']);
            $user->setCreateTs(
                $this->dateTimeService->fromString((string) $row['create_ts'])
            );
            $user->setFirstName((string) $row['first_name']);
            $user->setLastName((string) $row['last_name']);
            $user->setEmail((string) $row['email']);
            $user->setPhone((string) $row['phone']);
            $user->setWebsite((string) $row['website']);
            $user->setHash((string) $row['hash']);
            $user->setLocale((string) $row['locale']);
            $user->setLanguage((string) $row['language']);
            $user->setDeleted(
                1 === (int) $row['deleted']
            );
            $user->setLocked(
                1 === (int) $row['locked']
            );
            $user->setRoles(
                $this->rbacRepository->getRolesByUser($user)
            );
            $user->setLdapUser(
                $this->ldapUserRepository->isLDAPUser($user)
            );

            return $user;
        } catch (Exception $exception) {
            $message = 'error while getting user';
            $this->logger->error(
                $message
                , ['exception' => $exception]
            );
            throw new UserNotFoundException($message);
        } catch (TooManyRowsException $exception) {
            $message = 'too many users found';
            $this->logger->error(
                $message
                , [
                    'exception' => $exception
                    , 'mail'    => $email
                ]
            );
            throw new UserNotFoundException($message);
        }
    }

    /**
     * Returns an instance of IUser or null, if not found
     *
     * @param string $hash
     *
     * @return IUser
     * @throws UserNotFoundException
     */
    #[Override]
    public function getUserByHash(string $hash): IUser {
        try {
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
                    , 'u.locale'
                    , 'u.language'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'lock.state.user\') THEN true ELSE false END AS locked'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'delete.state.user\') THEN true ELSE false END AS deleted'
                ]
            )
                ->from('user', 'u')
                ->where('u.hash = ?')
                ->setParameter(0, $hash);
            $users     = $queryBuilder->executeQuery()->fetchAllAssociative();
            $userCount = count($users);

            if (0 === $userCount) {
                throw new UserNotFoundException();
            }

            if ($userCount > 1) {
                throw new TooManyRowsException("found more then one user for the given name");
            }

            $row  = $users[0];
            $user = new User();
            $user->setId((int) $row['id']);
            $user->setName((string) $row['name']);
            $user->setPassword((string) $row['password']);
            $user->setCreateTs(
                $this->dateTimeService->fromString((string) $row['create_ts'])
            );
            $user->setFirstName((string) $row['first_name']);
            $user->setLastName((string) $row['last_name']);
            $user->setEmail((string) $row['email']);
            $user->setPhone((string) $row['phone']);
            $user->setWebsite((string) $row['website']);
            $user->setHash((string) $row['hash']);
            $user->setLocale((string) $row['locale']);
            $user->setLanguage((string) $row['language']);
            $user->setDeleted(
                1 === (int) $row['deleted']
            );
            $user->setLocked(
                1 === (int) $row['locked']
            );
            $user->setRoles(
                $this->rbacRepository->getRolesByUser($user)
            );
            $user->setLdapUser(
                $this->ldapUserRepository->isLDAPUser($user)
            );

            return $user;
        } catch (Exception $exception) {
            $message = 'error while getting user';
            $this->logger->error(
                $message
                , [
                    'exception' => $exception
                    , 'hash'    => $hash
                ]
            );
            throw new UserNotFoundException($message);
        } catch (TooManyRowsException $exception) {
            $message = 'too many users found';
            $this->logger->error(
                $message
                , [
                    'exception' => $exception
                    , 'hash'    => $hash
                ]
            );
            throw new UserNotFoundException($message);
        }

    }

    /**
     * Removes an instance of IUser
     *
     * @param IUser $user
     *
     * @return IUser
     * @throws UserNotDeletedException
     */
    #[Override]
    public function remove(IUser $user): IUser {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('user')
                ->where('id = ?')
                ->setParameter(0, $user->getId())
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('error while deleting', ['exception' => $exception]);
            throw new UserNotDeletedException();
        }
        return $user;
    }

    /**
     * @param string $name
     * @return ArrayList
     * @throws UserException
     */
    #[Override]
    public function searchUsers(string $name): ArrayList {

        try {
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
                    , 'u.language'
                    , 'u.locale'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'delete.state.user\') THEN true ELSE false END AS deleted'
                    , 'CASE WHEN (SELECT 1 FROM user_state us WHERE us.user_id = u.id AND us.state = \'lock.state.user\') THEN true ELSE false END AS locked'
                ]
            )
                ->from('user', 'u')
                ->where('u.name like ?')
                ->setParameter(0, '%' . $name . '%');

            $result = $queryBuilder->executeQuery();
            $users  = $result->fetchAllAssociative();

            foreach ($users as $row) {

                $user = new User();
                $user->setId((int) $row['id']);
                $user->setName((string) $row['name']);
                $user->setPassword((string) $row['password']);
                $user->setCreateTs(
                    $this->dateTimeService->fromString((string) $row['create_ts'])
                );
                $user->setFirstName((string) $row['first_name']);
                $user->setLastName((string) $row['last_name']);
                $user->setEmail((string) $row['email']);
                $user->setPhone((string) $row['phone']);
                $user->setWebsite((string) $row['website']);
                $user->setHash((string) $row['hash']);
                $user->setLanguage((string) $row['language']);
                $user->setLocale((string) $row['locale']);
                $user->setDeleted(
                    1 === (int) $row['deleted']
                );
                $user->setLocked(
                    1 === (int) $row['locked']
                );
                $user->setRoles(
                    $this->rbacRepository->getRolesByUser($user)
                );
                $user->setLdapUser(
                    $this->ldapUserRepository->isLDAPUser($user)
                );

                $list->add($user);
            }

            return $list;
        } catch (Exception $exception) {
            $this->logger->error('error searching users', ['exception' => $exception]);
            throw new UserException();
        }
    }

}
