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

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash;
use Keestash\Core\DTO\Token\Token;
use Keestash\Exception\KeestashException;
use Keestash\Exception\Token\TokenNotCreatedException;
use Keestash\Exception\Token\TokenNotDeletedException;
use Keestash\Exception\Token\TokenNotFoundException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Log\LoggerInterface as ILogger;

class TokenRepository implements ITokenRepository {

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
        $this->userRepository  = $userRepository;
        $this->dateTimeService = $dateTimeService;
        $this->backend         = $backend;
        $this->logger          = $logger;
    }

    public function get(int $id): ?IToken {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'id'
                , 'name'
                , 'value'
                , 'user_id'
                , 'create_ts'
            ]
        )
            ->from('token')
            ->where('id = ?')
            ->setParameter(0, $id);
        $tokenData      = $queryBuilder->executeQuery()->fetchAllNumeric();
        $tokenDataCount = count($tokenData);

        if (0 === $tokenDataCount) {
            return null;
        }

        if ($tokenDataCount > 1) {
            throw new KeestashException("found more then one token for the given id");
        }

        $row      = $tokenData[0];
        $token    = null;
        $id       = $row[0];
        $name     = $row[1];
        $value    = $row[2];
        $userId   = $row[3];
        $createTs = $row[4];

        $user = $this->userRepository->getUserById((string) $userId);

        if (null == $user) {
            throw new KeestashException();
        }

        $token = new Token();
        $token->setId((int) $id);
        $token->setName($name);
        $token->setValue($value);
        $token->setUser($user);
        $token->setCreateTs($this->dateTimeService->fromFormat($createTs));

        return $token;
    }

    /**
     * @param string $hash
     * @return IToken
     * @throws TokenNotFoundException
     */
    public function getByValue(string $hash): IToken {
        try {
            $token        = new Token();
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $result       = $queryBuilder->select(
                [
                    'id'
                    , 'name'
                    , 'value'
                    , 'user_id'
                    , 'create_ts'
                ]
            )
                ->from('token')
                ->where('value = ?')
                ->setParameter(0, $hash)
                ->executeQuery();

            foreach ($result->fetchAllNumeric() as $row) {
                $id       = $row[0];
                $name     = $row[1];
                $value    = $row[2];
                $userId   = $row[3];
                $createTs = $row[4];

                $user = $this->userRepository->getUserById((string) $userId);

                $token->setId((int) $id);
                $token->setName($name);
                $token->setValue($value);
                $token->setUser($user);
                $token->setCreateTs($this->dateTimeService->fromFormat($createTs));
            }

            return $token;
        } catch (Exception $exception) {
            $this->logger->warning('error retrieving token', ['exception' => $exception]);
            throw new TokenNotFoundException();
        } catch (UserNotFoundException $exception) {
            $this->logger->error('error retrieving user for token', ['exception' => $exception]);
            throw new TokenNotFoundException();
        }
    }

    /**
     * @param IToken $token
     * @return IToken
     * @throws TokenNotCreatedException
     */
    public function add(IToken $token): IToken {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert("`token`")
                ->values(
                    [
                        "`name`"        => '?'
                        , "`value`"     => '?'
                        , "`user_id`"   => '?'
                        , "`create_ts`" => '?'
                    ]
                )
                ->setParameter(0, $token->getName())
                ->setParameter(1, $token->getValue())
                ->setParameter(2, $token->getUser()->getId())
                ->setParameter(3, $this->dateTimeService->toYMDHIS($token->getCreateTs()))
                ->executeStatement();

            $lastInsertId = (int) $this->backend->getConnection()->lastInsertId();

            if (0 === $lastInsertId) {
                throw new TokenNotCreatedException();
            }

            $token->setId($lastInsertId);
            return $token;
        } catch (Exception $exception) {
            $this->logger->error('error inserting token', ['exception' => $exception]);
            throw new TokenNotCreatedException();
        }
    }

    /**
     * @param IToken $token
     * @return IToken
     * @throws TokenNotDeletedException
     */
    public function remove(IToken $token): IToken {
        try {

            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete(
                'token'
            )
                ->where('id = ?')
                ->setParameter(0, $token->getId())
                ->executeStatement();
            return $token;
        } catch (Exception $exception) {
            $this->logger->error('error removing token', ['exception' => $exception]);
            throw new TokenNotDeletedException();
        }
    }

    /**
     * @param IUser $user
     * @return void
     * @throws TokenNotDeletedException
     */
    public function removeForUser(IUser $user): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete(
                'token'
            )
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('error removing token', ['exception' => $exception]);
            throw new TokenNotDeletedException();
        }
    }

}
