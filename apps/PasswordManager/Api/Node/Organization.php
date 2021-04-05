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

namespace KSA\PasswordManager\Api\Node;

use Exception;
use Keestash\Api\Response\LegacyResponse;
use KSA\GeneralApi\Repository\IOrganizationRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\Organization as OrganizationNodeRepository;
use KSP\Api\IResponse;
use KSP\Core\ILogger\ILogger;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Organization implements RequestHandlerInterface {

    private OrganizationNodeRepository $organization;
    private IOrganizationRepository    $organizationRepository;
    private NodeRepository             $nodeRepository;
    private ILogger                    $logger;
    private IL10N                      $translator;

    public function __construct(
        IL10N $l10n
        , OrganizationNodeRepository $organization
        , IOrganizationRepository $organizationRepository
        , NodeRepository $nodeRepository
        , ILogger $logger
    ) {
        $this->translator             = $l10n;
        $this->organization           = $organization;
        $this->organizationRepository = $organizationRepository;
        $this->nodeRepository         = $nodeRepository;
        $this->logger                 = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string) $request->getBody(), true);

        $nodeId         = $parameters['node_id'] ?? null;
        $organizationId = (int) ($parameters['organization_id'] ?? 0);

        $organization = $this->organizationRepository->get($organizationId);
        $node         = $this->nodeRepository->getNode((int) $nodeId, 0, 0);

        $orga = $node->getOrganization();

        if (null !== $orga) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    'message' => $this->translator->translate('The node belongs already to a organization')
                ]
            );
        }

        $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
        try {
            $this->organization->addNodeToOrganization(
                $node
                , $organization
            );
            $responseCode = IResponse::RESPONSE_CODE_OK;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage() . ': ' . $exception->getTraceAsString());
        }

        return LegacyResponse::fromData($responseCode, []);
    }
    
}