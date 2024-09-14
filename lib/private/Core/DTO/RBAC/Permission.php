<?php
declare(strict_types=1);

namespace Keestash\Core\DTO\RBAC;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Common\Interfaces\IComparable;
use doganoo\SimpleRBAC\Entity\PermissionInterface;
use KSP\Core\DTO\RBAC\IPermission;

class Permission implements IPermission {

    public function __construct(private readonly int                 $id, private readonly string            $name, private readonly DateTimeInterface $createTs)
    {
    }

    #[\Override]
    public function getId(): int {
        return $this->id;
    }

    #[\Override]
    public function getName(): string {
        return $this->name;
    }

    #[\Override]
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    #[\Override]
    public function compareTo($object): int {
        if (!$object instanceof PermissionInterface) {
            return IComparable::IS_LESS;
        }
        if ($this->getId() < $object->getId()) {
            return IComparable::IS_LESS;
        }
        if ($this->getId() == $object->getId()) {
            return IComparable::EQUAL;
        }
        if ($this->getId() > $object->getId()) {
            return IComparable::IS_GREATER;
        }
        return IComparable::IS_LESS;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            'id'          => $this->getId()
            , 'name'      => $this->getName()
            , 'create_ts' => $this->getCreateTs()
        ];
    }

}