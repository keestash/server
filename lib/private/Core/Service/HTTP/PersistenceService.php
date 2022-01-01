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

use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\CookieManager\ICookieManager;
use KSP\Core\Manager\SessionManager\ISessionManager;
use KSP\Core\Service\HTTP\IPersistenceService;

class PersistenceService implements IPersistenceService {

    private ISessionManager $sessionManager;
    private ICookieManager  $cookieManager;
    private ILogger         $logger;

    public function __construct(
        ISessionManager  $sessionManager
        , ICookieManager $cookieManager
        , ILogger        $logger
    ) {
        $this->sessionManager = $sessionManager;
        $this->cookieManager  = $cookieManager;
        $this->logger         = $logger;
    }

    public function getSessionValue(string $key, ?string $default = null): ?string {
        $this->logger->debug("getSessionValue: key $key, value $default");
        return $this->sessionManager->get($key, $default);
    }

    public function getCookieValue(string $key, ?string $default = null): ?string {
        $this->logger->debug("getCookieValue: key $key, value $default");
        return $this->cookieManager->get($key, $default);
    }

    public function getValue(string $key, ?string $default = null): ?string {
        $this->logger->debug("key $key, value $default");

        $sessionValue = $this->sessionManager->get($key, $default);
        $cookieValue  = $this->cookieManager->get($key, $default);

        if (null === $cookieValue && null === $sessionValue) return $default;

        return null === $cookieValue
            ? $sessionValue
            : $cookieValue;

    }

    public function setSessionValue(string $key, string $value): bool {
        $this->logger->debug("setSessionValue: key $key, value $value");
        return $this->sessionManager->set($key, $value);
    }

    public function setCookieValue(string $key, string $value, int $expireTs = 0): bool {
        $this->logger->debug("setCookieValue: key $key, value $value, expireTs $expireTs");
        return $this->cookieManager->set($key, $value, $expireTs);
    }

    public function isPersisted(string $key): bool {
        $this->logger->debug("key $key");
        $sessionPersisted = $this->getSessionValue($key);
        $cookiePersisted  = $this->getCookieValue($key);
        return null !== $sessionPersisted || null !== $cookiePersisted;
    }

    public function killAll(): void {
        $this->logger->debug("killing all");
        $this->sessionManager->killAll();
        $this->cookieManager->killAll();
    }

    public function setPersistenceValue(string $key, string $value, int $expireTs = 0): bool {
        $this->logger->debug("setPersistenceValue: key $key, value $value, expireTs $expireTs");
        $sessionPersisted = $this->setSessionValue($key, $value);
        $this->logger->debug("sessionPersisted: " . ($sessionPersisted ? 'true' : 'false'));
        $cookiePersisted = $this->setCookieValue($key, $value, $expireTs);
        $this->logger->debug("cookiePersisted: " . ($cookiePersisted ? 'true' : 'false'));
        return true === $sessionPersisted && true === $cookiePersisted;
    }

}
