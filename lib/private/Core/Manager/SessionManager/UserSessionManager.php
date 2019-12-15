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

use DateTime;
use doganoo\PHPUtil\HTTP\Session;
use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Core\Service\Config\ConfigService;

class UserSessionManager extends SessionManager {

    private const ONE_HOUR_IN_SECONDS   = 60 * 60;
    private const FIELD_NAME_USER_ID    = "user_id";
    private const FIELD_NAME_TIME_STAMP = "time_stamp";

    private $configService = null;

    public function __construct(
        Session $session
        , ConfigService $configService
    ) {
        parent::__construct($session);

        $this->configService = $configService;
    }

    public function isUserLoggedIn(): bool {
        FileLogger::debug((string) $this->getUser());
        FileLogger::debug((string) $this->getTimestamp());
        if (null === $this->getUser()) return false;
        if (null === $this->getTimestamp()) return false;

        $now      = new DateTime();
        $lifeTime = $this->configService->getValue(
            "user_lifetime"
            , UserSessionManager::ONE_HOUR_IN_SECONDS
        );

        FileLogger::debug("est");
        $ts = $this->getTimestamp();

        return ($now->getTimestamp() - $lifeTime) < $ts;
    }

    public function getUser(): ?string {
        FileLogger::debug(json_encode(parent::getAll()));
        return parent::get(
            UserSessionManager::FIELD_NAME_USER_ID
            , null
        );
    }

    public function getTimestamp(): ?int {
        $timeStamp = parent::get(
            UserSessionManager::FIELD_NAME_TIME_STAMP
            , null
        );
        if (null !== $timeStamp) return (int) $timeStamp;
        return $timeStamp;
    }

    public function setUserId(int $id): bool {
        return parent::set(
            UserSessionManager::FIELD_NAME_USER_ID
            , (string) $id
        );
    }

    public function updateTimestamp(): bool {
        return parent::set(
            UserSessionManager::FIELD_NAME_TIME_STAMP
            , (string) DateTimeUtil::getUnixTimestamp()
        );
    }

}