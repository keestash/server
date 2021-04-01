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

namespace KSA\PasswordManager\Entity\Share;

use DateTime;
use DateTimeInterface;
use JsonSerializable;

class PublicShare implements JsonSerializable {

    private int               $id;
    private string            $hash;
    private DateTimeInterface $expireTs;
    private int               $nodeId;

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getHash(): string {
        return $this->hash;
    }

    public function setHash(string $hash): void {
        $this->hash = $hash;
    }

    public function getExpireTs(): DateTimeInterface {
        return $this->expireTs;
    }

    public function setExpireTs(DateTimeInterface $expireTs): void {
        $this->expireTs = $expireTs;
    }

    public function isExpired(): bool {
        $today = new DateTime();
        return $this->getExpireTs()->getTimestamp() < $today->getTimestamp();
    }

    public function setNodeId(int $nodeId): void {
        $this->nodeId = $nodeId;
    }

    public function getNodeId(): int {
        return $this->nodeId;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return [
            "id"           => $this->getId()
            , "hash"       => $this->getHash()
            , "expire_ts"  => $this->getExpireTs()
            , "is_expired" => $this->isExpired()
            , "node_id"    => $this->getNodeId()
        ];
    }

}
