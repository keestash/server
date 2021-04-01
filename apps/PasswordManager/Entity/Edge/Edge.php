<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or
 * indirectly through a Keestash authorized reseller or distributor (a "Reseller"). Please read this EULA agreement
 * carefully before completing the installation process and using the Keestash software. It provides a license to use
 * the Keestash software and contains warranty information and liability disclaimers.
 */

namespace KSA\PasswordManager\Entity\Edge;

use DateTime;
use DateTimeInterface;
use doganoo\PHPAlgorithms\Common\Interfaces\IComparable;
use doganoo\PHPAlgorithms\Common\Util\Comparator;
use KSA\PasswordManager\Entity\Node;
use KSP\Core\DTO\Entity\IJsonObject;
use KSP\Core\DTO\User\IUser;

class Edge implements IJsonObject, IComparable {

    public const TYPE_REGULAR = "regular";
    public const TYPE_SHARE   = "share";

    private int                $id;
    private Node               $node;
    private ?Node              $parent   = null;
    private string             $type;
    private ?DateTimeInterface $expireTs = null;
    private ?DateTime          $createTs = null;
    private IUser              $owner;
    private IUser              $sharee;

    public function getCreateTs(): ?DateTime {
        return $this->createTs;
    }

    public function setCreateTs(?DateTime $createTs): void {
        $this->createTs = $createTs;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return [
            "id"          => $this->getId()
            , "node"      => $this->getNode()
            , "parent"    => $this->getParent()
            , "type"      => $this->getType()
            , "expire_ts" => $this->getExpireTs()
            , "owner"     => $this->getOwner()
            , "sharee"    => $this->getSharee()
        ];
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getNode(): Node {
        return $this->node;
    }

    public function setNode(Node $node): void {
        $this->node = $node;
    }

    public function getParent(): ?Node {
        return $this->parent;
    }

    public function setParent(Node $parent): void {
        $this->parent = $parent;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getExpireTs(): ?DateTimeInterface {
        return $this->expireTs;
    }

    public function setExpireTs(?DateTimeInterface $expireTs): void {
        $this->expireTs = $expireTs;
    }

    public function getOwner(): IUser {
        return $this->owner;
    }

    public function setOwner(IUser $owner): void {
        $this->owner = $owner;
    }

    public function getSharee(): IUser {
        return $this->sharee;
    }

    public function setSharee(IUser $sharee): void {
        $this->sharee = $sharee;
    }


    public function compareTo($object): int {
        if ($object instanceof Edge) {
            if (Comparator::equals($this->getCreateTs(), $object->getCreateTs())) return IComparable::EQUAL;
            if (Comparator::lessThan($this->getCreateTs(), $object->getCreateTs())) return IComparable::IS_LESS;
            if (Comparator::greaterThan($this->getCreateTs(), $object->getCreateTs())) return IComparable::IS_GREATER;
        }
        return IComparable::IS_LESS;
    }

}
