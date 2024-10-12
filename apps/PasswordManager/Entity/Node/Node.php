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

namespace KSA\PasswordManager\Entity\Node;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\PasswordManager\Entity\Share\PublicShare;
use KSA\PasswordManager\Entity\Share\Share;
use KSP\Core\DTO\Access\IAccessable;
use KSP\Core\DTO\Entity\IJsonObject;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;

abstract class Node implements IJsonObject, IAccessable {

    public const string ROOT       = "root";
    public const string FOLDER     = "folder";
    public const string CREDENTIAL = "credential";

    public const string ICON_FOLDER = "fas fa-folder";
    public const string ICON_ROOT   = "fas fa-tree";
    public const string ICON_KEY    = "fas fa-key";

    private int                $id           = 0;
    private string             $name         = "";
    private IUser              $user;
    private DateTimeInterface  $createTs;
    private ?DateTimeInterface $updateTs     = null;
    private string             $type;
    private ArrayList          $sharedTo;
    private ?IOrganization     $organization = null;
    private ?PublicShare       $publicShare  = null;

    public function __construct() {
        $this->setSharedTo(new ArrayList());
    }

    public function setSharedTo(ArrayList $sharedTo): void {
        $this->sharedTo = $sharedTo;
    }

    public function shareTo(Share $share): void {
        $this->sharedTo->add($share);
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    #[\Override]
    public function getUser(): IUser {
        return $this->user;
    }

    public function setUser(IUser $user): void {
        $this->user = $user;
    }

    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

    public function getUpdateTs(): ?DateTimeInterface {
        return $this->updateTs;
    }

    public function setUpdateTs(?DateTimeInterface $updateTs): void {
        $this->updateTs = $updateTs;
    }

    public abstract function getType(): string;

    public function setType(string $type): void {
        $this->type = $type;
    }

    public abstract function getIcon(): string;

    public function getValue(): int {
        return $this->getId();
    }

    public function getSharedTo(): ArrayList {
        return $this->sharedTo;
    }

    /**
     * @return ?IOrganization
     */
    #[\Override]
    public function getOrganization(): ?IOrganization {
        return $this->organization;
    }

    /**
     * @param ?IOrganization $organization
     */
    public function setOrganization(?IOrganization $organization): void {
        $this->organization = $organization;
    }

    public function isSharedTo(IUser $user): bool {
        return null !== $this->getShareByUser($user);
    }

    public function getShareByUser(IUser $user): ?Share {

        /** @var Share $share */
        foreach ($this->getSharedTo() as $share) {
            if ($share->getUser()->getId() === $user->getId()) return $share;
        }

        return null;
    }

    public function getPublicShare(): ?PublicShare {
        return $this->publicShare;
    }

    public function setPublicShare(?PublicShare $publicShare): void {
        $this->publicShare = $publicShare;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            "id"             => $this->getId()
            , "name"         => $this->getName()
            , "user_id"      => $this->getUser()->getId()
            , "user"         => $this->getUser()
            , "create_ts"    => $this->getCreateTs()
            , "type"         => $this->getType()
            , "icon"         => $this->getIcon()
            , "value"        => $this->getValue()
            , "shared_to"    => $this->getSharedTo()
            , "public_share" => $this->getPublicShare()
            , "organization" => $this->getOrganization()
            , "update_ts"    => $this->getUpdateTs()
        ];
    }

}
