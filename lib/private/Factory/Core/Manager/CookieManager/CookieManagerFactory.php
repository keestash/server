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

namespace Keestash\Factory\Core\Manager\CookieManager;

use Keestash\Core\Manager\CookieManager\CookieManager;
use Keestash\Core\Service\HTTP\HTTPService;
use KSP\Core\Manager\CookieManager\ICookieManager;
use KSP\Core\Service\Config\IConfigService;
use Psr\Container\ContainerInterface;

class CookieManagerFactory {

    public function __invoke(ContainerInterface $container): ICookieManager {
        return new CookieManager(
            $container->get(HTTPService::class)
            , $container->get(IConfigService::class)
        );
    }

}