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
use KSA\PasswordManager\Entity\Node;

class ShareService {

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

}
