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
use Keestash\Api\AbstractApi;
use KSA\PasswordManager\Application\Application;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Navigation\DefaultEntry;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Node as NodeEntity;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\Cache\ICacheService;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\L10N\IL10N;

/**
 * Class Node
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Get extends AbstractApi {

    private NodeRepository    $nodeRepository;
    private NodeService       $nodeService;
    private ICacheService     $cacheServer;
    private ILogger           $logger;
    private BreadCrumbService $breadCrumbService;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , NodeService $nodeService
        , ICacheService $cacheServer
        , ILogger $logger
        , BreadCrumbService $breadCrumbService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeRepository    = $nodeRepository;
        $this->nodeService       = $nodeService;
        $this->cacheServer       = $cacheServer;
        $this->logger            = $logger;
        $this->breadCrumbService = $breadCrumbService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $root = null;

        $rootId = $this->getParameter("id");

        if (null === $rootId) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no root id given. Could not retrieve data")
                ]
            );
            parent::setResponse($response);
            return;
        }

//        if ($this->cacheServer->exists($rootId)) {
//            $root = unserialize($this->cacheServer->get($rootId));
//        } else {
        $root = $this->prepareNode();
//            $this->cacheServer->set($rootId, serialize($root));
//        }

        if ($root->getUser()->getId() !== $this->getToken()->getUser()->getId()) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    'message' => $this->getL10N()->translate('unauthorized')
                    , 'root'  => $root->getUser()->getId()
                    , 'token' => $this->getToken()->getUser()->getId()
                ]
            );
            return;
        }

//        $this->logger->debug($root->getId() . ' has ' . $root->);
        $response = parent::createResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "breadCrumb" => $this->breadCrumbService->getBreadCrumbs($root, $this->getToken()->getUser())
                , "message"  => $this->getL10N()->translate("Ok")
                , "node"     => $root
            ]
        );

        parent::setResponse($response);

    }

    private function prepareNode(): NodeEntity {
        $id = $this->getParameter("id");

        // base case 1: we are requesting a regular node.
        //      select and return
        if (true === is_numeric($id)) {
            return $this->nodeRepository->getNode((int) $id, 0, 1);
        }

        $root = $this->nodeRepository->getRootForUser(
            $this->getToken()->getUser()
            , 0
            , 1
        );

        // base case 2: we are requesting the root. No need to do the following stuff
        if ($id === Application::ROOT_FOLDER) {
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
                    if (true === $node->isSharedToMe()) {
                        $newEdges->add($node);
                    }
                }
                $root->setEdges($newEdges);
                break;
        }

        return $root;
    }

    public function afterCreate(): void {

    }

}
