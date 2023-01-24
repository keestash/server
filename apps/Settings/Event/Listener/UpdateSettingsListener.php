<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\Settings\Event\Listener;

use KSA\Settings\Event\SettingsChangedEvent;
use KSA\Settings\Exception\SettingNotFoundException;
use KSA\Settings\Exception\SettingsException;
use KSA\Settings\Repository\SettingsRepository;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface;

class UpdateSettingsListener implements IListener {

    public function __construct(
        private readonly SettingsRepository $settingsRepository
        , private readonly LoggerInterface  $logger
    ) {
    }

    public function execute(IEvent $event): void {

        if (false === ($event instanceof SettingsChangedEvent)) {
            throw new SettingsException('invalid event');
        }

        $value = null;
        try {
            $value = $this->settingsRepository->get($event->getSetting()->getKey());
        } catch (SettingNotFoundException $exception) {
            $this->logger->info(
                'setting not found'
                , [
                    'event'       => $event
                    , 'exception' => $exception
                ]
            );
        }

        if (null === $value) {
            $this->settingsRepository->add(
                $event->getSetting()
            );
        }

        if (null !== $value && true === $event->isOverride()) {
            $this->settingsRepository->remove($event->getSetting()->getKey());
            $this->settingsRepository->add(
                $event->getSetting()
            );
        }

        {
            $this->logger->info(
                'setting updated/inserted'
                , [
                    'event'   => $event
                    , 'value' => $value
                ]
            );
        }

    }

}