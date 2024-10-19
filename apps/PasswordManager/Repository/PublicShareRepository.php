<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

namespace KSA\PasswordManager\Repository;

use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Entity\Share\NullShare;
use KSA\PasswordManager\Entity\Share\PublicShare;
use KSA\PasswordManager\Exception\Node\Share\ShareException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use Psr\Log\LoggerInterface;

final readonly class PublicShareRepository {

    public function __construct(
        private IBackend           $backend
        , private IDateTimeService $dateTimeService
        , private LoggerInterface  $logger
    ) {
    }

    public function shareNode(Node $node): Node {
        $share = $node->getPublicShare();

        if (null === $share) {
            throw new PasswordManagerException();
        }

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert("`pwm_public_share`")
            ->values(
                [
                    "`node_id`"     => '?'
                    , "`hash`"      => '?'
                    , "`expire_ts`" => '?'
                    , "`password`"  => '?'
                    , "`secret`"    => '?'
                ]
            )
            ->setParameter(0, $node->getId())
            ->setParameter(1, $share->getHash())
            ->setParameter(2, $this->dateTimeService->toYMDHIS($share->getExpireTs()))
            ->setParameter(3, $share->getPassword())
            ->setParameter(4, $share->getSecret())
            ->executeStatement();

        $shareId = (int) $this->backend->getConnection()->lastInsertId();

        if (0 === $shareId) {
            throw new PasswordManagerException();
        }

        $node->setPublicShare(
            new PublicShare(
                $shareId,
                $node->getId(),
                $share->getHash(),
                $share->getExpireTs(),
                $share->getPassword(),
                $share->getSecret()
            )
        );
        return $node;
    }

    public function getShare(string $hash): PublicShare {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                's.id'
                , 's.hash'
                , 's.expire_ts'
                , 's.node_id'
                , 's.password'
                , 's.secret'
            ]
        )
            ->from('pwm_public_share', 's')
            ->where('s.`hash` = ?')
            ->setParameter(0, $hash);

        $result = $queryBuilder->executeQuery();
        $rows   = $result->fetchAllNumeric();

        if (0 === count($rows)) {
            return new NullShare();
        }

        $row = $rows[0];

        return new PublicShare(
            (int) $row[0],
            (int) $row[3],
            (string) $row[1],
            $this->dateTimeService->fromFormat((string) $row[2]),
            (string) $row[4],
            (string) $row[5],
        );

    }

    public function getShareById(int $id): PublicShare {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                's.id'
                , 's.hash'
                , 's.expire_ts'
                , 's.node_id'
                , 's.password'
                , 's.secret'
            ]
        )
            ->from('pwm_public_share', 's')
            ->where('s.`id` = ?')
            ->setParameter(0, $id);

        $result = $queryBuilder->executeQuery();
        $rows   = $result->fetchAllNumeric();

        if (0 === count($rows)) {
            return new NullShare();
        }

        $row       = $rows[0];
        $shareId   = $row[0];
        $shareHash = $row[1];
        $expireTs  = $row[2];
        $nodeId    = $row[3];
        $password  = $row[4];
        $secret    = $row[5];

        return new PublicShare(
            (int) $shareId,
            (int) $nodeId,
            (string) $shareHash,
            $this->dateTimeService->fromFormat($expireTs),
            (string) $password,
            (string) $secret,
        );

    }

    public function getShareByNode(Node $node): PublicShare {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                's.id'
                , 's.hash'
                , 's.expire_ts'
                , 's.node_id'
                , 's.password'
                , 's.secret'
            ]
        )
            ->from('pwm_public_share', 's')
            ->where('s.`node_id` = ?')
            ->setParameter(0, $node->getId());

        $result = $queryBuilder->executeQuery();
        $rows   = $result->fetchAllNumeric();

        if (0 === count($rows)) {
            return new NullShare();
        }

        $row = $rows[0];

        return new PublicShare(
            (int) $row[0],
            (int) $row[3],
            (string) $row[1],
            $this->dateTimeService->fromFormat($row[2]),
            (string) $row[4],
            (string) $row[5],
        );
    }

    public function addShareInfo(Node $node): Node {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    's.id'
                    , 's.hash'
                    , 's.expire_ts'
                    , 's.node_id'
                    , 's.password'
                    , 's.secret'
                ]
            )
                ->from('pwm_public_share', 's')
                ->where('s.`node_id` = ?')
                ->andWhere('s.`expire_ts` >= CURRENT_TIMESTAMP')
                ->setParameter(0, $node->getId());

            $result = $queryBuilder->executeQuery();
            $rows   = $result->fetchAllNumeric();

            if (0 === count($rows)) {
                $node->setPublicShare(null);
                return $node;
            }

            $row       = $rows[0];
            $shareId   = $row[0];
            $shareHash = $row[1];
            $expireTs  = $row[2];
            $nodeId    = $row[3];
            $password  = $row[4];
            $secret    = $row[5];

            $node->setPublicShare(
                new PublicShare(
                    (int) $shareId,
                    (int) $nodeId,
                    (string) $shareHash,
                    $this->dateTimeService->fromFormat((string) $expireTs),
                    (string) $password,
                    (string) $secret
                )
            );
            return $node;
        } catch (Exception $e) {
            $this->logger->warning('can not request share info', ['node' => $node, 'exception' => $e]);
            throw new PasswordManagerException();
        }
    }

    public function removeByUser(IUser $user): bool {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            return $queryBuilder->delete('pwm_public_share', 'pps')
                    ->where('pps.`node_id` IN (
                                SELECT DISTINCT n.`id` FROM `pwm_node` n WHERE n.`user_id` = ?
                        )')
                    ->setParameter(0, $user->getId())
                    ->executeStatement() !== 0;
        } catch (Exception $e) {
            $this->logger->warning('can not remove users public share', ['user' => $user, 'exception' => $e]);
            return false;
        }
    }

    public function remove(PublicShare $share): PublicShare {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('pwm_public_share', 'pps')
                ->where('id = ?')
                ->setParameter(0, $share->getId())
                ->executeStatement();
            return $share;
        } catch (Exception $e) {
            $this->logger->warning('can not remove users public share', ['share' => $share, 'exception' => $e]);
            throw new ShareException();
        }
    }

    public function removeOutdated(): bool {
        $now = $this->dateTimeService->toYMDHIS(new DateTimeImmutable());
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            return $queryBuilder->delete('pwm_public_share')
                    ->where('`expire_ts` < ?')
                    ->setParameter(0, $now)
                    ->executeStatement() !== 0;
        } catch (Exception $e) {
            $this->logger->warning('can not remove outdated', ['exception' => $e]);
            return false;
        }
    }

}
