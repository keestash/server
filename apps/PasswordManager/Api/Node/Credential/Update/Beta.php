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

namespace KSA\PasswordManager\Api\Node\Credential\Update;

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Encryption\Password\Password;
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Event\Node\Credential\CredentialUpdatedEvent;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\Service\Event\IEventService;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Update
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 * TODO
 *      handle non existent parameters
 *      handle more fields
 */
final readonly class Beta implements RequestHandlerInterface {

    public function __construct(
        private NodeRepository     $nodeRepository
        , private IActivityService $activityService
        , private IEventService    $eventService
    ) {
    }

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $name       = $parameters["name"] ?? null;
        $username   = $parameters["username"] ?? null;
        $url        = $parameters["url"] ?? null;
        $nodeId     = $parameters["nodeId"] ?? null;
        $password   = $parameters["password"] ?? null;

        $node       = $this->nodeRepository->getNode((int) $nodeId);
        $hasChanges = false;

        if (false === ($node instanceof Credential)) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $node->setName($name);

        if (null !== $username) {
            $node->setUsername(base64_decode($username));
            $node->setUpdateTs(new DateTimeImmutable());
            $hasChanges = true;
        }

        if (null !== $url) {
            $node->setUrl(base64_decode($username));
            $node->setUpdateTs(new DateTimeImmutable());
            $hasChanges = true;
        }

        if (null !== $password) {
            $node->setPassword(base64_decode($password));

            $corePassword = new Password();
            $corePassword->setValue((string) $node->getPassword());
            $corePassword->setCharacterSet([]);

            // todo currently, there is no entropy measured
            $node->setEntropy(base64_decode(''));

            $node->setUpdateTs(new DateTimeImmutable());
            $hasChanges = true;
        }

        if (false === $hasChanges) {
            return new JsonResponse([], IResponse::NOT_MODIFIED);
        }

        $node = $this->nodeRepository->updateCredential($node);
        $this->eventService->execute(
            new CredentialUpdatedEvent(
                $node,
                new DateTimeImmutable()
            )
        );

        /** @var Node $parent */
        $parent = $this->nodeRepository->getParentNode($node->getId());

        $parent->setUpdateTs(new DateTimeImmutable());
        $this->nodeRepository->updateNode($parent);

        $this->activityService->insertActivityWithSingleMessage(
            ConfigProvider::APP_ID
            , (string) $node->getId()
            , "updated credential"
        );

        return new JsonResponse([], IResponse::OK);
    }


}
