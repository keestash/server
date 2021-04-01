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

namespace KSA\PasswordManager\Entity;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use JsonSerializable;
use Keestash;
use Keestash\Core\Service\Validation\Rule\Numerical;
use Keestash\Core\Service\Validation\Validator\ValidatorBag;
use KSA\PasswordManager\Entity\Share\PublicShare;
use KSA\PasswordManager\Entity\Share\Share;
use KSP\Core\DTO\Entity\IValidatable;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use Laminas\Validator\NotEmpty;

abstract class Node implements JsonSerializable, IValidatable {

    public const ROOT       = "root";
    public const FOLDER     = "folder";
    public const CREDENTIAL = "credential";

    public const ICON_FOLDER = "fas fa-folder";
    public const ICON_ROOT   = "fas fa-tree";
    public const ICON_KEY    = "fas fa-key";

    private int               $id           = 0;
    private string            $name         = "";
    private IUser             $user;
    private DateTimeInterface $createTs;
    private string            $type;
    private ArrayList         $sharedTo;
    private ?IOrganization    $organization = null;
    private ?PublicShare      $publicShare  = null;

    public function __construct() {
        $this->sharedTo = new ArrayList();
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

    public abstract function getType(): string;

    public function setType($type): void {
        $this->type = $type;
    }

    public abstract function getIcon(): string;

    public function getValue() {
        return $this->getId();
    }

    public function getSharedTo(): ArrayList {
        return $this->sharedTo;
    }

    /**
     * @return ?IOrganization
     */
    public function getOrganization(): ?IOrganization {
        return $this->organization;
    }

    /**
     * @param ?IOrganization $organization
     */
    public function setOrganization(?IOrganization $organization): void {
        $this->organization = $organization;
    }

    public function isSharedToMe(): bool {
        // TODO find a better solution :(
        $loggedInUser = Keestash::getServer()->getUserFromSession();
        if (null === $loggedInUser) return false;
        return $this->isSharedTo($loggedInUser);
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

    public function getValidators(): array {
        $bags = [];

        // id
        $validatorBag = new ValidatorBag();
        $validatorBag->setValue($this->id);
        $validatorBag->addValidator(new NotEmpty());
        $validatorBag->addValidator(new Numerical());
        $bags[] = $validatorBag;

        // name
        $validatorBag = new ValidatorBag();
        $validatorBag->setValue($this->name);
        $validatorBag->addValidator(new NotEmpty());
        $bags[] = $validatorBag;

        // userid
        $validatorBag = new ValidatorBag();
        $validatorBag->setValue($this->getUser()->getId());
        $validatorBag->addValidator(new NotEmpty());
        $validatorBag->addValidator(new Numerical());
        $bags[] = $validatorBag;

        return $bags;
    }

    public function jsonSerialize(): array {
        return [
            "id"                => $this->getId()
            , "name"            => $this->getName()
            , "user_id"         => $this->getUser()->getId()
            , "user"            => $this->getUser()
            , "create_ts"       => $this->getCreateTs()
            , "type"            => $this->getType()
            , "icon"            => $this->getIcon()
            , "value"           => $this->getValue()
            , "shared_to"       => $this->getSharedTo()
            , "is_shared_to_me" => $this->isSharedToMe()
            , "public_share"    => $this->getPublicShare()
            , "organization"    => $this->getOrganization()
        ];
    }

}
