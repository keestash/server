<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or indirectly through a Keestash authorized reseller or distributor (a "Reseller").
 * Please read this EULA agreement carefully before completing the installation process and using the Keestash software. It provides a license to use the Keestash software and contains warranty information and liability disclaimers.
 */

namespace KSA\PasswordManager\Repository;

use doganoo\DI\DateTime\IDateTimeService;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Share\PublicShare;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;

class PublicShareRepository {

    private IDateTimeService $dateTimeService;
    private ILogger          $logger;
    private IBackend         $backend;

    public function __construct(
        IBackend $backend
        , IDateTimeService $dateTimeService
        , ILogger $logger
    ) {
        $this->dateTimeService = $dateTimeService;
        $this->logger          = $logger;
        $this->backend         = $backend;
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
                ]
            )
            ->setParameter(0, $node->getId())
            ->setParameter(1, $share->getHash())
            ->setParameter(2, $this->dateTimeService->toYMDHIS($share->getExpireTs()))
            ->execute();

        $shareId = (int) $this->backend->getConnection()->lastInsertId();

        if (0 === $shareId) {
            throw new PasswordManagerException();
        }

        $share->setId($shareId);
        $node->setPublicShare($share);
        return $node;
    }

    public function getShare(string $hash): ?PublicShare {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                's.id'
                , 's.hash'
                , 's.expire_ts'
                , 's.node_id'
            ]
        )
            ->from('pwm_public_share', 's')
            ->where('s.`hash` = ?')
            ->setParameter(0, $hash);

        $result = $queryBuilder->executeQuery();
        $rows   = $result->fetchAllNumeric();

        if (0 === count($rows)) {
            return null;
        }

        $row       = $rows[0];
        $shareId   = $row[0];
        $shareHash = $row[1];
        $expireTs  = $row[2];
        $nodeId    = $row[3];

        $publicShare = new PublicShare();
        $publicShare->setId((int) $shareId);
        $publicShare->setHash((string) $shareHash);
        $publicShare->setExpireTs($this->dateTimeService->fromFormat($expireTs));
        $publicShare->setNodeId((int) $nodeId);

        return $publicShare;
    }

    public function getShareByNode(Node $node): ?PublicShare {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                's.id'
                , 's.hash'
                , 's.expire_ts'
                , 's.node_id'
            ]
        )
            ->from('pwm_public_share', 's')
            ->where('s.`node_id` = ?')
            ->setParameter(0, $node->getId());

        $result = $queryBuilder->executeQuery();
        $rows   = $result->fetchAllNumeric();

        if (0 === count($rows)) {
            return null;
        }

        $row       = $rows[0];
        $shareId   = $row[0];
        $shareHash = $row[1];
        $expireTs  = $row[2];
        $nodeId    = $row[3];

        $publicShare = new PublicShare();
        $publicShare->setId((int) $shareId);
        $publicShare->setHash((string) $shareHash);
        $publicShare->setExpireTs($this->dateTimeService->toDateTime((int) $expireTs));
        $publicShare->setNodeId((int) $nodeId);

        return $publicShare;
    }

    public function addShareInfo(Node $node): Node {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                's.id'
                , 's.hash'
                , 's.expire_ts'
                , 's.node_id'
            ]
        )
            ->from('pwm_public_share', 's')
            ->where('s.`node_id` = ?')
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

        $publicShare = new PublicShare();
        $publicShare->setId((int) $shareId);
        $publicShare->setHash((string) $shareHash);
        $publicShare->setExpireTs($this->dateTimeService->fromFormat($expireTs));
        $publicShare->setNodeId((int) $nodeId);

        $node->setPublicShare($publicShare);
        return $node;
    }

    public function removeByUser(IUser $user): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('pwm_public_share', 'pps')
                ->where('pps.`node_id` IN (
                                SELECT DISTINCT n.`id` FROM `pwm_node` n WHERE n.`user_id` = ?
                        )')
                ->setParameter(0, $user->getId())
                ->execute() !== 0;
    }

    public function removeOutdated(): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        return $queryBuilder->delete('pwm_public_share', 'pps')
                ->where('pps.`expire_ts` < NOW()')
                ->execute() !== 0;
    }

}
