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

namespace KSA\PasswordManager\Api\Node\Credential;

use doganoo\PHPUtil\HTTP\Code;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

/**
 * Class Credential
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Get extends AbstractApi {

    private NodeRepository    $nodeRepository;
    private CredentialService $credentialService;

    public function __construct(
        IL10N $l10n
        , CredentialService $credentialService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);
        $this->credentialService = $credentialService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {

        $nodeId = (int) $this->getParameter("id");

        $node = $this->nodeRepository->getNode($nodeId, 1);
        if (null === $node || $node->getUser()->getId() !== $this->getToken()->getUser()->getId()) return;

        $msg = new DefaultResponse();
        $msg->setCode(Code::OK);
        $msg->addMessage(
            IResponse::RESPONSE_CODE_OK
            , [
                "response_code" => IResponse::RESPONSE_CODE_OK
                , "decrypted"   => $this->credentialService->getDecryptedPassword($node)
            ]
        );

        $this->setResponse($msg);

    }

    public function afterCreate(): void {

    }

}
