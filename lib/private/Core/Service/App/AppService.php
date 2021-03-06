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

namespace Keestash\Core\Service\App;

use Keestash\App\App;
use Keestash\ConfigProvider;
use KSP\App\IApp;
use KSP\Core\Service\App\IAppService;

class AppService implements IAppService {

    public function toApp(string $id, array $data): IApp {
        $app = new App();
        $app->setId($id);
        $app->setName((string) $data[ConfigProvider::APP_NAME]);
        $app->setOrder((int) $data[ConfigProvider::APP_ORDER]);
        $app->setVersion((int) $data[ConfigProvider::APP_VERSION]);
        $app->setBaseRoute((string) $data[ConfigProvider::APP_BASE_ROUTE]);
        return $app;
    }

}