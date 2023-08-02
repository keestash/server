<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Core\Service\Event\Listener;

use doganoo\SimpleRBAC\Entity\RoleInterface;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Service\Event\Listener\IListener;

class RolesAndPermissionsListener implements IListener {


    public function __construct(private readonly RBACRepositoryInterface $rbacRepository) {
    }

    public function execute(IEvent $event): void {
        if (false === ($event instanceof UserCreatedEvent)) {
            return;
        }

        $this->rbacRepository->assignRoleToUser(
            $event->getUser()
            , $this->rbacRepository->getRole(RoleInterface::DEFAULT)
        );
    }

}