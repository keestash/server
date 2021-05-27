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

namespace KSA\PasswordManager\Event\Listener\PublicShare;

use Exception;
use Keestash\Core\Service\Core\Event\ApplicationStartedEvent;
use Keestash\Core\Service\Instance\InstallerService;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IListener;
use Symfony\Contracts\EventDispatcher\Event;

class RemoveExpired implements IListener {

    private PublicShareRepository $publicShareRepository;
    private InstallerService      $installerService;
    private ILogger               $logger;

    public function __construct(
        PublicShareRepository $publicShareRepository
        , InstallerService $installerService
        , ILogger $logger
    ) {
        $this->publicShareRepository = $publicShareRepository;
        $this->installerService      = $installerService;
        $this->logger                = $logger;
    }

    /**
     * @param ApplicationStartedEvent $event
     */
    public function execute(Event $event): void {
        if (false === $this->installerService->hasIdAndHash()) return;
        try {
            $this->publicShareRepository->removeOutdated();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

}