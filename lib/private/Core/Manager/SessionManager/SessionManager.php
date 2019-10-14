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

namespace Keestash\Core\Manager\SessionManager;

use Keestash\Core\Service\DateTimeService;
use KSP\Core\Backend\IBackend;
use KSP\Core\Manager\SessionManager\ISessionManager;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionManager implements ISessionManager {

    private $session = null;

    public function __construct(?IBackend $backend, ?DateTimeService $dateTimeService = null) {
        $this->session = new Session();
    }

    /**
     * @param string $name
     * @param        $value
     * @return bool
     */
    public function set(string $name, $value): bool {
        $this->session->set($name, $value);
        return true;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name) {
        return $this->session->get($name, null);
    }

    public function destroy() {
        $this->session->clear();
    }

}