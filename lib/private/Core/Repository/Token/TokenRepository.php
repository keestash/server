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

use doganoo\DI\DateTime\IDateTimeService;
use Keestash;
use Keestash\Core\DTO\Token\Token;
use Keestash\Core\Repository\AbstractRepository;
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;

class TokenRepository extends AbstractRepository implements ITokenRepository {

    private IUserRepository  $userRepository;
    private IDateTimeService $dateTimeService;

    public function __construct(
        IBackend $backend
        , IUserRepository $userRepository
        , IDateTimeService $dateTimeService
    ) {
        parent::__construct($backend);
        $this->userRepository  = $userRepository;
        $this->dateTimeService = $dateTimeService;
    }

    public function get(int $id): ?IToken {
        $queryBuilder = $this->getQueryBuilder();
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
        $tokenData      = $queryBuilder->execute()->fetchAll();
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

        $token = new Token();
        $token->setId((int) $id);
        $token->setName($name);
        $token->setValue($value);
        $token->setUser(
            $this->userRepository->getUserById($userId)
        );
        $token->setCreateTs($this->dateTimeService->fromFormat($createTs));

        return $token;
    }

    public function getByHash(string $hash): ?IToken {
        $token        = null;
        $queryBuilder = $this->getQueryBuilder();
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
            ->execute();

        foreach ($result->fetchAllNumeric() as $row) {
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
            $token->setCreateTs($this->dateTimeService->fromFormat($createTs));
        }

        return $token;
    }

    public function add(IToken $token): ?int {
        $queryBuilder = $this->getQueryBuilder();
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
            ->execute();

        $lastInsertId = (int) $this->getLastInsertId();

        if (0 === $lastInsertId) return null;
        return $lastInsertId;
    }

    public function remove(IToken $token): bool {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->delete(
                'token'
            )
                ->where('id = ?')
                ->setParameter(0, $token->getId())
                ->execute() !== 0;
    }

    public function removeForUser(IUser $user): bool {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->delete(
                'token'
            )
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->execute() !== 0;
    }

}
