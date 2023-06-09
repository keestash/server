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

use Doctrine\DBAL\Exception;
use Keestash\Core\DTO\Event\ApplicationStartedEvent;
use Keestash\Exception\Repository\Derivation\DerivationNotFoundException;
use Keestash\Exception\Repository\NoRowsFoundException;
use Keestash\Exception\Token\TokenNotDeletedException;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface;

class RemoveOutdatedTokens implements IListener {

    public function __construct(
        private readonly ITokenRepository        $tokenRepository
        , private readonly IDerivationRepository $derivationRepository
        , private readonly LoggerInterface       $logger
    ) {

    }

    /**
     * @param IEvent|ApplicationStartedEvent $event
     * @return void
     * @throws Exception
     */
    public function execute(IEvent|ApplicationStartedEvent $event): void {
        $tokens = $this->tokenRepository->getOlderThan(
            $event->getDateTime()->modify('-1 day')
        );

        /** @var IToken $token */
        foreach ($tokens as $token) {
            try {
                $this->tokenRepository->remove($token);
                $this->derivationRepository->remove(
                    $this->derivationRepository->get(
                        $token->getUser()
                    )
                );
            } catch (TokenNotDeletedException $e) {
                $this->logger->error('token not deleted', ['exception' => $e]);
            } catch (NoRowsFoundException|DerivationNotFoundException $e) {
                $this->logger->debug('no rows found', ['exception' => $e]);
            }
        }
    }

}