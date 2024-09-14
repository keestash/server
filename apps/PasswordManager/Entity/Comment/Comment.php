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

namespace KSA\PasswordManager\Entity\Comment;

use DateTimeInterface;
use KSA\PasswordManager\Entity\Node\Node;
use KSP\Core\DTO\Entity\IJsonObject;
use KSP\Core\DTO\Entity\JWT;
use KSP\Core\DTO\User\IUser;

class Comment implements IJsonObject, JWT {

    private int               $id;
    private string            $comment;
    private Node              $node;
    private IUser             $user;
    private DateTimeInterface $createTs;
    private ?string           $jwt = null;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getComment(): string {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void {
        $this->comment = $comment;
    }

    /**
     * @return Node
     */
    public function getNode(): Node {
        return $this->node;
    }

    /**
     * @param Node $node
     */
    public function setNode(Node $node): void {
        $this->node = $node;
    }

    /**
     * @return IUser
     */
    public function getUser(): IUser {
        return $this->user;
    }

    /**
     * @param IUser $user
     */
    public function setUser(IUser $user): void {
        $this->user = $user;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    /**
     * @param DateTimeInterface $createTs
     */
    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

    public function setJWT(?string $jwt): void {
        $this->jwt = $jwt;
    }

    #[\Override]
    public function getJWT(): ?string {
        return $this->jwt;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\Override]
    public function jsonSerialize(): array {
        return [
            "id"          => $this->getId()
            , "comment"   => $this->getComment()
            , "node"      => $this->getNode()
            , "user"      => $this->getUser()
            , "create_ts" => $this->getCreateTs()
            , 'jwt'       => $this->getJWT()
        ];
    }

}
