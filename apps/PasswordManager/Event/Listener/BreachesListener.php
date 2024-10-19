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

namespace KSA\PasswordManager\Event\Listener;

use DateTimeImmutable;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use Keestash\Exception\EncryptionFailedException;
use Keestash\Exception\Repository\Derivation\DerivationException;
use KSA\PasswordManager\Api\Node\Pwned\ChangeState;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Pwned\Breaches;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Service\Node\PwnedService;
use KSA\Settings\Exception\SettingNotFoundException;
use KSA\Settings\Repository\IUserSettingRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface;

class BreachesListener implements IListener {

    public function __construct(
        private readonly PwnedService              $pwnedService
        , private readonly PwnedBreachesRepository $pwnedBreachesRepository
        , private readonly LoggerInterface         $logger
        , private readonly IUserSettingRepository  $userSettingRepository
    ) {
    }

    #[\Override]
    public function execute(IEvent $event): void {
        return;
        $this->logger->debug('start handle breaches');
        $candidates = $this->pwnedBreachesRepository->getOlderThan(
            (new DateTimeImmutable())->modify('-30 min')
        );

        /** @var Breaches $candidate */
        foreach ($candidates as $candidate) {
            if (false === $this->isActiveForUser($candidate->getNode()->getUser())) {
                $this->logger->debug('breach check is deactivated for user. Skipping', ['userId' => $candidate->getNode()->getUser()->getId()]);
                continue;
            }

            $breachFound = [];
            $this->logger->debug(sprintf('processing %s', $candidate->getNode()->getId()));

            try {
                $node = $candidate->getNode();

                if (false === ($node instanceof Credential)) {
                    continue;
                }

                // TODO find a way
                $breachFound = $this->pwnedService->importBreaches($node->getUsername());

            } catch (ClientException|RequestException $e) {

                $response = $e->getResponse();
                if (null === $response) {
                    throw $e;
                }

                if ($response->getStatusCode() !== IResponse::NOT_FOUND) {
                    $this->logger->error(
                        'error while retrieving data from breaches api'
                        , [
                            'statusCode' => $response->getStatusCode()
                            , 'body'     => (string) $response->getBody()
                            , 'uri'      => (string) $e->getRequest()->getUri()
                        ]
                    );
                    throw $e;
                }
                $this->logger->info('all great here, nothing found :)');

            } catch (PasswordManagerException|InvalidNodeTypeException|JsonException|EncryptionFailedException|DerivationException $e) {
                $this->logger->error('error importing breaches', ['exception' => $e]);
                continue;
            }

            $this->pwnedBreachesRepository->replace(
                new Breaches(
                    $candidate->getNode()
                    , count($breachFound) > 0
                    ? $breachFound
                    : null
                    , $candidate->getCreateTs()
                    , new DateTimeImmutable()
                )
            );

            sleep(10);

        }
    }

    private function isActiveForUser(IUser $user): bool {
        $setting = null;
        try {
            $setting = $this->userSettingRepository->get(ChangeState::USER_SETTING_PWNED_ACTIVE, $user);
        } catch (SettingNotFoundException $e) {
            $this->logger->debug('no setting found', ['exception' => $e]);
            $setting = null;
        }
        return null !== $setting;
    }

}
