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

use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Repository\Session\ISessionRepository;
use SessionHandlerInterface;

class SessionHandler implements SessionHandlerInterface {

    /** @var ISessionRepository|AbstractRepository */
    private $sessionRepository = null;

    public function __construct(ISessionRepository $sessionRepository) {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @inheritDoc
     */
    public function open($save_path, $name) {
        return $this->sessionRepository->open();
    }

    /**
     * @inheritDoc
     */
    public function read($session_id) {
        return $this->sessionRepository->get($session_id);
    }

    /**
     * @inheritDoc
     */
    public function write($session_id, $session_data) {
        return $this->sessionRepository->replace($session_id, $session_data);
    }

    /**
     * @inheritDoc
     */
    public function gc($maxlifetime) {
        return $this->sessionRepository->deleteByLastUpdate($maxlifetime);
    }

    /**
     * @inheritDoc
     */
    public function destroy($session_id) {
        return $this->sessionRepository->deleteById($session_id);
    }

    /**
     * @inheritDoc
     */
    public function close() {
        return $this->sessionRepository->close();
    }

}