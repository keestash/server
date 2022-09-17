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
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Navigation\DefaultEntry;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Entity\Node\Node as NodeEntity;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use Laminas\Diactoros\Response\JsonResponse;
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

    private NodeRepository           $nodeRepository;
    private BreadCrumbService        $breadCrumbService;
    private ILogger                  $logger;
    private NodeEncryptionService    $nodeEncryptionService;
    private CommentRepository        $commentRepository;
    private PwnedPasswordsRepository $pwnedPasswordsRepository;
    private PwnedBreachesRepository  $pwnedBreachesRepository;

    public function __construct(
        NodeRepository             $nodeRepository
        , BreadCrumbService        $breadCrumbService
        , ILogger                  $logger
        , NodeEncryptionService    $nodeEncryptionService
        , CommentRepository        $commentRepository
        , PwnedPasswordsRepository $pwnedPasswordsRepository
        , PwnedBreachesRepository  $pwnedBreachesRepository
    ) {
        $this->nodeRepository           = $nodeRepository;
        $this->breadCrumbService        = $breadCrumbService;
        $this->logger                   = $logger;
        $this->nodeEncryptionService    = $nodeEncryptionService;
        $this->commentRepository        = $commentRepository;
        $this->pwnedPasswordsRepository = $pwnedPasswordsRepository;
        $this->pwnedBreachesRepository  = $pwnedBreachesRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        /** @var IToken $token */
        $token  = $request->getAttribute(IToken::class);
        $rootId = $request->getAttribute("node_id");

        if (null === $rootId) {
            return new JsonResponse(
                ["no node id given. Could not retrieve data"]
                , IResponse::NOT_FOUND
            );
        }

        try {
            $root = $this->prepareNode($rootId, $token);
        } catch (PasswordManagerException|InvalidNodeTypeException $exception) {
            $this->logger->error($exception->getMessage());
            return new JsonResponse(
                ['no data found']
                , IResponse::NOT_FOUND
            );
        }

        return new JsonResponse(
            [
                "breadCrumb" => $this->breadCrumbService->getBreadCrumbs($root, $token->getUser())
                , "node"     => $root
                // comments should be a part of node, since attachments are also
                // a part of node. The other way around (and probably the better)
                // is to extract comments, shares, attachments etc. from node
                // and provide them as a standalone service/endpoint.
                //
                // However, the comments in the response here are actually needed
                // for the app. Therefore, we will have this here until we find a
                // proper solution (to the text above).
                , "comments" => $this->commentRepository->getCommentsByNode($root)
                // as with comments, pwned are part of node. However, since we do
                // not want to blow up the node/credential, we retrieve them as separate
                // field.
                //
                // We should extract pwned as standalone services as well.
                // However, the comments in the response here are actually needed
                // for the app. Therefore, we will have this here until we find a
                // proper solution (to the text above).
                , "pwned"    => [
                'passwords'  => $this->pwnedPasswordsRepository->getPwnedByNode($root)->toArray()
                , 'breaches' => $this->pwnedBreachesRepository->getPwnedByNode($root)->toArray()
            ]
            ]
            , IResponse::OK
        );

    }

    /**
     * @param string $id
     * @param IToken $token
     * @return NodeEntity
     * @throws PasswordManagerException
     * @throws InvalidNodeTypeException
     */
    private function prepareNode(string $id, IToken $token): NodeEntity {

        // base case 1: we are requesting a regular node.
        //      select and return
        if (true === is_numeric($id)) {
            $node = $this->nodeRepository->getNode(
                (int) $id
                , 0
                , 2
            );
            $this->nodeEncryptionService->decryptNode($node);
            return $node;
        }

        $root = $this->nodeRepository->getRootForUser(
            $token->getUser()
            , 0
            , 2
        );

        // base case 2: we are requesting the root. No need to do the following stuff
        if (Node::ROOT === $id) {
            $this->nodeEncryptionService->decryptNode($root);
            return $root;
        }

        // regular cases: we are requesting one of the defaults. Check!
        switch ($id) {
            case DefaultEntry::DEFAULT_ENTRY_RECENTLY_MODIFIED:

                $edges = $root->getEdges()->toArray();

                usort(
                    $edges
                    , static function (Edge $current, Edge $next): int {
                    /** @var DateTime $currentTs */
                    $currentTs = null !== $current->getNode()->getUpdateTs()
                        ? $current->getNode()->getUpdateTs()
                        : new DateTime();
                    /** @var DateTime $nextTs */
                    $nextTs = null !== $next->getNode()->getUpdateTs()
                        ? $next->getNode()->getUpdateTs()
                        : new DateTime();
                    return $nextTs->getTimestamp() - $currentTs->getTimestamp();
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
            default:
                throw new PasswordManagerException('unknown operation ' . $id);
        }

        $this->nodeEncryptionService->decryptNode($root);
        return $root;
    }

}
