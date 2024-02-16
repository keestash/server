<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\Login\Factory\Api\Login;

use KSA\Login\Api\Login\Alpha;
use KSA\Login\Api\Login\Login;
use KSP\Core\Service\Metric\ICollectorService;
use Psr\Container\ContainerInterface;

class LoginFactory {

    public function __invoke(ContainerInterface $container): Login {
        return new Login(
            $container->get(Alpha::class)
            , $container->get(ICollectorService::class)
        );
    }

}
