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

namespace KSA\PasswordManager\Api\Share;

use Keestash\Api\AbstractApi;
use Keestash\Core\Service\Encryption\Key\KeyService;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class PublicShareSingle extends AbstractApi {

    private PublicShareRepository $shareRepository;
    private NodeRepository        $nodeRepository;
    private IUserRepository       $userRepository;
    private KeyService            $keyService;
    private EncryptionService     $encryptionService;
    private CredentialService     $credentialService;

    public function __construct(
        IL10N $l10n
        , PublicShareRepository $shareRepository
        , NodeRepository $nodeRepository
        , IUserRepository $userRepository
        , KeyService $keyService
        , EncryptionService $encryptionService
        , CredentialService $credentialService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->shareRepository   = $shareRepository;
        $this->nodeRepository    = $nodeRepository;
        $this->userRepository    = $userRepository;
        $this->keyService        = $keyService;
        $this->encryptionService = $encryptionService;
        $this->credentialService = $credentialService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $hash = $this->getParameter('hash');

        if (null === $hash) {
            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no password found for hash")
                    , "hash"  => $hash
                ]
            );
            return;
        }

        $share = $this->shareRepository->getShare($hash);

        if (null === $share || $share->isExpired()) {
            parent::createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no share found")
                ]
            );
            return;
        }

        /** @var Credential $node */
        $node = $this->nodeRepository->getNode($share->getNodeId(), 0, 1);

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "response_code" => IResponse::RESPONSE_CODE_OK
                , "decrypted"   => $this->credentialService->getDecryptedPassword($node)
            ]
        );


    }

    public function afterCreate(): void {

    }

}
