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

use doganoo\PHPUtil\HTTP\Session;
use KSP\Core\Manager\SessionManager\ISessionManager;

class SessionManager implements ISessionManager {

    private $session = null;

    public function __construct(Session $session) {
        $this->session = $session;
    }

    /**
     * @param string $name
     * @param        $value
     * @return bool
     */
    public function set(string $name, string $value): bool {
        $this->session->start();
        $this->session->set($name, $value);
        return true;
    }

    /**
     * @param string      $name
     * @param string|null $default
     * @return string|null
     */
    public function get(string $name, ?string $default = null): ?string {
        $this->session->start();
        return $this->session->get($name, $default);
    }

    public function getAll(): array {
        $this->session->start();
        return $this->session->getAll();
    }

    public function destroy(): void {
        $this->session->start();
        $this->session->destroy();
    }

    public function killAll(): void {
        $this->session->start();
        $this->destroy();
    }

    /**
     * @param string $name
     */
    public function kill(string $name): void {
        $this->session->start();
        $this->session->remove($name);
    }

}