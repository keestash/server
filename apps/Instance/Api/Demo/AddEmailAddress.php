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

namespace KSA\Instance\Api\Demo;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\Repository\RowNotInsertedException;
use Keestash\Exception\Validator\ValidationFailedException;
use KSA\Instance\Repository\DemoUsersRepository;
use KSP\Api\IResponse;
use KSP\Core\Service\Metric\ICollectorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class AddEmailAddress implements RequestHandlerInterface {

    public function __construct(
        private DemoUsersRepository $demoUsersRepository
        , private UserService       $userService
        , private ICollectorService $collectorService
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ValidationFailedException
     * @throws RowNotInsertedException
     */
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $email      = $parameters['email'] ?? '';

        $hasEmailAddress = $this->demoUsersRepository->hasEmailAddress($email);

        if (true === $hasEmailAddress) {
            return new JsonResponse([], IResponse::CONFLICT);
        }

        if (false === $this->userService->validEmail($email)) {
            throw new ValidationFailedException('invalid email');
        }

        $this->demoUsersRepository->add($email);

        $this->collectorService->addCounter(
            'addDemoUser'
        );

        return new JsonResponse(
            []
            , IResponse::OK
        );
    }

}
