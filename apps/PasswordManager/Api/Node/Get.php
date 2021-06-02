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

namespace KSA\PasswordManager\Api\Node;

use DateTime;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Navigation\DefaultEntry;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Node as NodeEntity;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Node
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Get implements RequestHandlerInterface {

    private IL10N             $translator;
    private NodeRepository    $nodeRepository;
    private BreadCrumbService $breadCrumbService;
    private ILogger           $logger;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , BreadCrumbService $breadCrumbService
        , ILogger $logger
    ) {
        $this->translator        = $l10n;
        $this->nodeRepository    = $nodeRepository;
        $this->breadCrumbService = $breadCrumbService;
        $this->logger            = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        /** @var IToken $token */
        $token  = $request->getAttribute(IToken::class);
        $rootId = $request->getAttribute("id");

        if (null === $rootId) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no root id given. Could not retrieve data")
                ]
            );
        }

        $root = $this->prepareNode($request, $token);

        if ($root->getUser()->getId() !== $token->getUser()->getId()) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    'message' => $this->translator->translate('unauthorized')
                    , 'root'  => $root->getUser()->getId()
                    , 'token' => $token->getUser()->getId()
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "breadCrumb" => $this->breadCrumbService->getBreadCrumbs($root, $token->getUser())
                , "message"  => $this->translator->translate("Ok")
                , "node"     => $root
            ]
        );

    }

    private function prepareNode(ServerRequestInterface $request, IToken $token): NodeEntity {
        $id = $request->getAttribute("id");

        // base case 1: we are requesting a regular node.
        //      select and return
        if (true === is_numeric($id)) {
            return $this->nodeRepository->getNode((int) $id, 0, 1);
        }

        $root = $this->nodeRepository->getRootForUser(
            $token->getUser()
            , 0
            , 1
        );

        // base case 2: we are requesting the root. No need to do the following stuff
        if (Node::ROOT === $id) {
            return $root;
        }

        // regular cases: we are requesting one of the defaults. Check!
        switch ($id) {
            case DefaultEntry::DEFAULT_ENTRY_RECENTLY_MODIFIED:

                $edges = $root->getEdges()->toArray();

                usort(
                    $edges
                    , static function (Edge $current, Edge $next): bool {
                    $currentTs = null !== $current->getCreateTs()
                        ? $current->getCreateTs()
                        : new DateTime();
                    $nextTs    = null !== $next->getCreateTs()
                        ? $next->getCreateTs()
                        : new DateTime();
                    return $currentTs < $nextTs;
                });

                $edgez = new ArrayList();
                $edgez->addAllArray($edges);
                $root->setEdges($edgez);
                break;

            case DefaultEntry::DEFAULT_ENTRY_SHARED_WITH_ME:

                $newEdges = new ArrayList();
                /** @var Edge $edge */
                foreach ($root->getEdges() as $edge) {

                    $node = $edge->getNode();
                    if (true === $node->isSharedTo($token->getUser())) {
                        $newEdges->add($node);
                    }
                }
                $root->setEdges($newEdges);
                break;
        }

        return $root;
    }

}
