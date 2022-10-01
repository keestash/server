<?php
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

use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require_once __DIR__ . '/lib/start.php';

/** @var IUserService $userService */
$userService = $container->get(IUserService::class);
/** @var IUserRepository $userRepository */
$userRepository = $container->get(IUserRepository::class);

$user = $userRepository->getUserById("7");
dump($user->getName());
$password = 'a9e80d53c775abec87b8fbffa306b79c';
$hashed   = $userService->hashPassword($password);
dump($password);
dump($hashed);
dump($userService->verifyPassword($password, $hashed));
