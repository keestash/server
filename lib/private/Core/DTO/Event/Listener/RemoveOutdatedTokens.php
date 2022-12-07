<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Core\DTO\Event\Listener;

use Keestash\Core\DTO\Event\ApplicationStartedEvent;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Service\Event\Listener\IListener;

class RemoveOutdatedTokens implements IListener {

    private ITokenRepository $tokenRepository;

    public function __construct(ITokenRepository $tokenRepository) {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @param IEvent|ApplicationStartedEvent $event
     * @return void
     */
    public function execute(IEvent $event): void {
        $tokens = $this->tokenRepository->getOlderThan(
            $event->getDateTime()->modify('-1 day')
        );

        /** @var IToken $token */
        foreach ($tokens as $token) {
            $this->tokenRepository->remove($token);
        }
    }

}