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

namespace Keestash\Core\Service\HTTP;

use doganoo\PHPUtil\Log\FileLogger;
use KSP\Core\Manager\CookieManager\ICookieManager;
use KSP\Core\Manager\SessionManager\ISessionManager;

class PersistenceService {

    /** @var ISessionManager|null $sessionManager */
    private $sessionManager = null;
    /** @var ICookieManager|null $cookieManager */
    private $cookieManager = null;

    public function __construct(
        ISessionManager $sessionManager
        , ICookieManager $cookieManager
    ) {
        $this->sessionManager = $sessionManager;
        $this->cookieManager  = $cookieManager;
    }

    public function getSessionValue(string $key, ?string $default = null): ?string {
        return $this->sessionManager->get($key, $default);
    }

    public function getCookieValue(string $key, ?string $default = null): ?string {
        return $this->cookieManager->get($key, $default);
    }

    public function getValue(string $key, ?string $default = null): ?string {

        $sessionValue = $this->sessionManager->get($key, $default);
        $cookieValue  = $this->cookieManager->get($key, $default);

        FileLogger::debug("session Value: $sessionValue");
        FileLogger::debug("cookie Value: $cookieValue");

        return null === $cookieValue || $default === $cookieValue
            ? $sessionValue
            : $cookieValue;

    }

    public function setSessionValue(string $key, string $value): bool {
        return $this->sessionManager->set($key, $value);
    }

    public function setCookieValue(string $key, string $value, int $expireTs = 0): bool {
        return $this->cookieManager->set($key, $value, $expireTs);
    }

    public function isPersisted(string $key): bool {
        $sessionPersisted = $this->getSessionValue($key, 'false');
        $cookiePersisted  = $this->getCookieValue($key, 'false');
        return 'false' !== $sessionPersisted && 'false' !== $cookiePersisted;
    }

    public function killAll(): void {
        $this->sessionManager->killAll();
        $this->cookieManager->killAll();
    }

    public function setPersistenceValue(string $key, string $value, int $expireTs = 0): bool {
        $sessionPersisted = $this->setSessionValue($key, $value);
        $cookiePersisted  = $this->setCookieValue($key, $value, $expireTs);
        FileLogger::debug("session set: $sessionPersisted, cookie set: $cookiePersisted");
        return true === $sessionPersisted && true === $cookiePersisted;
    }

}