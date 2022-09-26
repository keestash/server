<?php
declare(strict_types=1);

namespace Keestash\Core\DTO\RBAC;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Common\Interfaces\IComparable;
use doganoo\SimpleRBAC\Entity\PermissionInterface;
use KSP\Core\DTO\RBAC\IPermission;

class Permission implements IPermission {

    private int               $id;
    private string            $name;
    private DateTimeInterface $createTs;

    public function __construct(
        int                 $id
        , string            $name
        , DateTimeInterface $createTs
    ) {
        $this->id       = $id;
        $this->name     = $name;
        $this->createTs = $createTs;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

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

    public function jsonSerialize(): array {
        return [
            'id'          => $this->getId()
            , 'name'      => $this->getName()
            , 'create_ts' => $this->getCreateTs()
        ];
    }

}