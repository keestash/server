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

namespace KSA\PasswordManager\Command\Node;

use DateTimeImmutable;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use Keestash\Command\KeestashCommand;
use Keestash\Exception\EncryptionFailedException;
use Keestash\Exception\Repository\Derivation\DerivationException;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Pwned\Api\Passwords;
use KSA\PasswordManager\Entity\Node\Pwned\Breaches;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSA\PasswordManager\Service\Node\PwnedService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Api\IResponse;
use KSP\Command\IKeestashCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPwned extends KeestashCommand {

    public const ARGUMENT_NAME_TYPE = 'type';

    public function __construct(
        private readonly PwnedService               $pwnedService
        , private readonly PwnedPasswordsRepository $pwnedPasswordsRepository
        , private readonly PwnedBreachesRepository  $pwnedBreachesRepository
        , private readonly NodeRepository           $nodeRepository
        , private readonly NodeEncryptionService    $nodeEncryptionService
        , private readonly LoggerInterface          $logger
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("password-manager:pwned:import")
            ->setDescription("imports hbip data import")
            ->addArgument(
                ImportPwned::ARGUMENT_NAME_TYPE
                , InputArgument::REQUIRED
                , 'the type of the import (breaches, passwords)'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {

        $type = $input->getArgument(ImportPwned::ARGUMENT_NAME_TYPE);
        if ($type === 'breaches') {
            $this->handleBreaches($output);
        } else if ($type === 'passwords') {
            $this->handlePasswords($output);
        } else {
            throw new PasswordManagerException();
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function handleBreaches(OutputInterface $output): void {
        $this->writeInfo('start handle breaches', $output);
        $candidates = $this->pwnedBreachesRepository->getOlderThan(
            (new DateTimeImmutable())->modify('-30 min')
        );

        /** @var Breaches $candidate */
        foreach ($candidates as $candidate) {
            $breachFound = [];
            $this->writeInfo(sprintf('processing %s', $candidate->getNode()->getId()), $output);
            try {
                $node = $candidate->getNode();

                if (false === ($node instanceof Credential)) {
                    continue;
                }


                $this->nodeEncryptionService->decryptNode($node);

                $breachFound = $this->pwnedService->importBreaches($node->getUsername()->getPlain());

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
                $this->writeInfo('all great here, nothing found :)', $output);

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

    private function handlePasswords(OutputInterface $output): void {
        $candidates = $this->pwnedPasswordsRepository->getOlderThan(
            (new DateTimeImmutable())->modify('-30 min')
        );

        /** @var \KSA\PasswordManager\Entity\Node\Pwned\Passwords $candidate */
        foreach ($candidates as $candidate) {
            try {
                $this->writeInfo(sprintf('processing %s', $candidate->getNode()->getId()), $output);
                $credential = $this->nodeRepository->getNode($candidate->getNode()->getId());

                if (false === ($credential instanceof Credential)) {
                    continue;
                }

                $this->nodeEncryptionService->decryptNode($credential);

                $plainPassword = $credential->getPassword()->getPlain();

                $searchHash = $this->pwnedService->generateSearchHash($plainPassword);
                $this->writeInfo(sprintf('Search Hash %s', $searchHash), $output);
                $passwordTree = $this->pwnedService->importPasswords($searchHash);

                $this->nodeEncryptionService->decryptNode($credential);

                $passwordsNode = $passwordTree->search(
                    new Passwords(
                        strtoupper(substr(
                            sha1((string) $plainPassword)
                            , 0
                            , 5
                        ))
                        , strtoupper(substr(
                            sha1((string) $plainPassword)
                            , 5
                        ))
                        , 0
                    )
                );

                if (null !== $passwordsNode) {
                    $this->writeInfo('password leak found', $output);
                }

                $this->pwnedPasswordsRepository->replace(
                    new \KSA\PasswordManager\Entity\Node\Pwned\Passwords(
                        $this->nodeRepository->getNode($candidate->getNode()->getId(), 0, 0)
                        , null !== $passwordsNode
                        ? (int) floor($passwordsNode->getValue()->getCount() % 10)
                        : 0
                        , $candidate->getCreateTs()
                        , new DateTimeImmutable()
                    )
                );

                sleep(10);
            } catch (EncryptionFailedException|DerivationException $e) {
                $this->logger->warning(
                    'passwords import failed'
                    , [
                        'exception' => $e
                    ]
                );
            }
        }
    }

}