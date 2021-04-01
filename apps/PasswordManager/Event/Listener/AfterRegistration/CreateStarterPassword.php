<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\PasswordManager\Event\Listener\AfterRegistration;

use doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException;
use doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException;
use Keestash\Exception\KeestashException;
use Keestash\Legacy\Legacy;
use KSA\PasswordManager\Exception\DefaultPropertiesNotSetException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Core\DTO\User\IUser;
use KSP\L10N\IL10N;

class CreateStarterPassword {

    public const FIRST_CREDENTIAL_ID = 1;
    public const ROOT_ID             = 1;

    private Legacy            $legacy;
    private NodeRepository    $nodeRepository;
    private NodeService       $nodeService;
    private CredentialService $credentialService;
    private IL10N             $translator;

    public function __construct(
        Legacy $legacy
        , NodeRepository $nodeRepository
        , NodeService $nodeService
        , CredentialService $credentialService
        , IL10N $translator
    ) {
        $this->legacy            = $legacy;
        $this->nodeRepository    = $nodeRepository;
        $this->nodeService       = $nodeService;
        $this->credentialService = $credentialService;
        $this->translator        = $translator;
    }

    /**
     * @param IUser $user
     * @throws DefaultPropertiesNotSetException
     * @throws KeestashException
     * @throws InvalidKeyTypeException
     * @throws UnsupportedKeyTypeException
     */
    public function run(IUser $user): void {

        $root = $this->nodeService->createRootFolder(
            CreateStarterPassword::ROOT_ID
            , $user
        );

        $rootId = $this->nodeRepository->addRoot($root);

        if (null === $rootId) {
            throw new KeestashException("could not create root folder");
        }

        $credential = $this->credentialService->createCredential(
            $this->legacy->getApplication()->get('name')
            , (string) $this->legacy->getApplication()->get("web")
            , $user->getName()
            , (string) $this->legacy->getApplication()->get("name")
            , $user
            , $root
            , $this->translator->translate('This is your first Keestash password')
        );
        $credential->setId(CreateStarterPassword::FIRST_CREDENTIAL_ID);

        $inserted = $this->credentialService->insertCredential($credential, $root);

        if (null === $inserted) {
            throw new DefaultPropertiesNotSetException("could not create edge");
        }
    }

}
