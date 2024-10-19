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

namespace KSA\PasswordManager\Service\Node\Share;

use DateTime;
use DateTimeInterface;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Entity\Share\NullShare;
use KSA\PasswordManager\Entity\Share\PublicShare;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;

final readonly class ShareService {

    public function __construct(
        private NodeRepository  $nodeRepository,
        private IUserRepository $userRepository,
        private IUserService    $userService
    ) {
    }

    public function generateSharingHash(Node $node): string {
        return hash_hmac(
            'sha256'
            , uniqid("", true) . (new DateTime())->getTimestamp()
            , $node->getUser()->getPassword()
        );
    }

    public function getDefaultExpireDate(): DateTimeInterface {
        $dateTime = new DateTime();
        $dateTime->modify("+3 day");
        return $dateTime;
    }

    public function isExpired(PublicShare $publicShare): bool {
        if ($publicShare instanceof NullShare) {
            return false;
        }
        $today = new DateTime();
        return $publicShare->getExpireTs()->getTimestamp() < $today->getTimestamp();
    }

    public function createPublicShare(
        Node              $node,
        DateTimeInterface $dateTime,
        string            $password,
        string            $secret
    ): PublicShare {
        return new PublicShare(
            0,
            $node->getId(),
            $this->generateSharingHash($node),
            $dateTime,
            $password,
            $secret
        );
    }

    /**
     * @param int    $nodeId
     * @param string $userId
     *
     * @return bool
     *
     * TODO add more properties
     */
    public function isShareable(int $nodeId, string $userId): bool {
        try {
            $node = $this->nodeRepository->getNode($nodeId, 0, 1);
        } catch (PasswordManagerException) {
            return false;
        }

        $user = $this->userRepository->getUserById($userId);

        return
            false === $this->userService->isDisabled($user)
            && false === $node->getUser()->equals($user)
            && false === $node->isSharedTo($user);
    }

}
