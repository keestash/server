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

namespace KSA\PasswordManager\Event\Listener;

use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Core\System\Application;
use Keestash\Exception\FolderNotCreatedException;
use Keestash\Exception\Key\KeyNotCreatedException;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Event\Listener\AfterRegistration\CreateStarterPassword;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface;

/**
 * Class AfterRegistration
 *
 * @package KSA\PasswordManager\Hook
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 *
 */
class AfterRegistration implements IListener {

    public const FIRST_CREDENTIAL_ID = 1;
    public const ROOT_ID             = 1;

    private IKeyService       $keyService;
    private LoggerInterface   $logger;
    private NodeService       $nodeService;
    private NodeRepository    $nodeRepository;
    private CredentialService $credentialService;
    private Application       $application;

    public function __construct(
        IKeyService         $keyService
        , LoggerInterface   $logger
        , NodeService       $nodeService
        , NodeRepository    $nodeRepository
        , CredentialService $credentialService
        , Application       $application
    ) {
        $this->keyService        = $keyService;
        $this->logger            = $logger;
        $this->nodeService       = $nodeService;
        $this->nodeRepository    = $nodeRepository;
        $this->credentialService = $credentialService;
        $this->application       = $application;
    }

    /**
     * @param UserCreatedEvent $event
     */
    public function execute(IEvent $event): void {

        // base case: we do not create stuff for the system user
        if ($event->getUser()->getId() === IUser::SYSTEM_USER_ID) {
            return;
        }

        try {
            $this->keyService->createAndStoreKey($event->getUser());
            $this->logger->info('key created');
            $root = $this->createRootFolder($event);
            $this->logger->info('folder created');
            $this->createStarterPassword($event, $root);
            $this->logger->info('password created');
        } catch (KeyNotCreatedException $e) {
            $this->logger->error('key not created', ['exception' => $e]);
            $this->keyService->remove($event->getUser());
            $this->nodeRepository->removeForUser($event->getUser());
        } catch (PasswordManagerException|FolderNotCreatedException $exception) {
            $this->logger->error('password/folder not created', ['exception' => $exception]);
            $this->keyService->remove($event->getUser());
            $this->nodeRepository->removeForUser($event->getUser());
        }

    }

    /**
     * @param IEvent $event
     * @return Root
     * @throws FolderNotCreatedException
     */
    private function createRootFolder(IEvent $event): Root {
        $root = $this->nodeService->createRootFolder(
            AfterRegistration::ROOT_ID
            , $event->getUser()
        );

        $rootId = $this->nodeRepository->addRoot($root);

        if (null === $rootId) {
            throw new FolderNotCreatedException("could not create root folder");
        }
        return $root;
    }

    /**
     * @param IEvent $event
     * @param Root   $root
     * @return void
     * @throws PasswordManagerException
     */
    private function createStarterPassword(IEvent $event, Root $root): void {
        $credential = $this->credentialService->createCredential(
            (string) $this->application->getMetaData()->get('name')
            , (string) $this->application->getMetaData()->get("web")
            , $event->getUser()->getName()
            , (string) $this->application->getMetaData()->get("name")
            , $event->getUser()
        );
        $credential->setId(AfterRegistration::FIRST_CREDENTIAL_ID);
        $this->credentialService->insertCredential($credential, $root);
    }

}
