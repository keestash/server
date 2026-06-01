<?php
declare(strict_types=1);

namespace Keestash\ThirdParty\SimpleRbac\Service;

use Keestash\ThirdParty\SimpleRbac\Entity\PermissionInterface;
use Keestash\ThirdParty\SimpleRbac\Entity\RoleInterface;
use Keestash\ThirdParty\SimpleRbac\Entity\UserInterface;
use Keestash\ThirdParty\SimpleRbac\Repository\RBACRepositoryInterface;

class RBACService implements RBACServiceInterface {

    private RBACRepositoryInterface $rbacRepository;

    public function __construct(
        RBACRepositoryInterface $rbacRepository
    ) {
        $this->rbacRepository = $rbacRepository;
    }

    public function getPermission(int $permissionId): PermissionInterface {
        return $this->rbacRepository->getPermission($permissionId);
    }

    public function hasPermission(UserInterface $user, PermissionInterface $permission): bool {
        /** @var RoleInterface $role */
        foreach ($user->getRoles()->toArray() as $role) {
            if (true === $role->getPermissions()->contains($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasRole(UserInterface $user, RoleInterface $role): bool {
        return $user->getRoles()->contains($role);
    }

}