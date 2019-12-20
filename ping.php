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

include 'vendor/autoload.php';
include 'lib/Keestash.php';

Keestash::init();
$handler = Keestash::getServer()->query(\SessionHandlerInterface::class);
/** @var \KSP\Core\Repository\Session\ISessionRepository $repo */
$repo = Keestash::getServer()->query(\KSP\Core\Repository\Session\ISessionRepository::class);

var_dump(session_set_save_handler($handler, false));
session_start();
$_SESSION['dogan'] = __FILE__;
session_write_close();

var_dump($_SESSION);
var_dump($repo->getAll());
echo json_encode(time());