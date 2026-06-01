<?php
declare(strict_types=1);

namespace Keestash\ThirdParty\SimpleRbac\Service;

use Keestash\ThirdParty\SimpleRbac\Entity\PermissionInterface;
use Keestash\ThirdParty\SimpleRbac\Entity\RoleInterface;
use Keestash\ThirdParty\SimpleRbac\Entity\UserInterface;

interface RBACServiceInterface {

    public function getPermission(int $permissionId): PermissionInterface;

    public function hasPermission(UserInterface $user, PermissionInterface $permission): bool;

    public function hasRole(UserInterface $user, RoleInterface $role): bool;

}