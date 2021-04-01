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
use KSP\App\IApp;
use KSP\Core\Service\App\ILoaderService;

class LoaderService implements ILoaderService {

    private string $appRoot;

    public function __construct(string $appRoot) {
        $this->appRoot = $appRoot;
    }

    public function buildApp(array $info): IApp {
        $app = new App();
        $app->setId($info[IApp::FIELD_ID]);
        $app->setName($info[IApp::FIELD_NAME]);
        $app->setFAIconClass($info[IApp::FIELD_FA_ICON_CLASS]);
        $app->setOrder((int) $info[IApp::FIELD_ORDER]);
        $app->setAppPath("{$this->appRoot}apps/{$app->getId()}");
        $app->setTemplatePath("{$app->getAppPath()}/template/");
        $app->setStringPath("{$app->getAppPath()}/string/");
        $app->setVersion((int) $info[IApp::FIELD_VERSION]);
        $app->setVersionString($info[IApp::FIELD_VERSION_STRING]);
        $showIcon = $info[IApp::FIELD_SHOW_ICON] ?? 0;
        $app->setShowIcon($showIcon === 1);
        $app->setDemonstratable((int) ($info[IApp::FIELD_DEMONSTRATE] ?? 0) === 1);
        return $app;
    }

}