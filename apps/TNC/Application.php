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

namespace KSA\TNC;

use KSA\TNC\Controller\Controller;

class Application extends \Keestash\App\Application {

    public const TERMS_AND_CONDITIONS = "tnc";

    public function register(): void {
        $this->registerRoute(Application::TERMS_AND_CONDITIONS, Controller::class);
        $this->registerPublicRoute(Application::TERMS_AND_CONDITIONS);
    }

}