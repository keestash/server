<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Pwned;

use Keestash\Api\Response\JsonResponse;
use KSA\Settings\Exception\SettingNotFoundException;
use KSA\Settings\Repository\IUserSettingRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

#[OA\Get(
    path: '/password_manager/node/pwned/is_active',
    operationId: 'passwordManagerPwnedIsActive',
    summary: 'Check whether pwned password checking is active for the current user',
    tags: ['Password Manager - Pwned'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Active state',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'active', type: 'boolean'),
                ]
            )
        ),
    ],
    security: [['tokenAuth' => [], 'userAuth' => []]]
)]
class IsActive implements RequestHandlerInterface {

    public function __construct(
        private readonly IUserSettingRepository $userSettingRepository
        , private readonly LoggerInterface      $logger
    ) {
    }

    #[\Override]
    public
    function handle(ServerRequestInterface $request): ResponseInterface {

        $user    = $request->getAttribute(IToken::class)->getUser();
        $setting = null;
        try {
            $setting = $this->userSettingRepository->get(ChangeState::USER_SETTING_PWNED_ACTIVE, $user);
        } catch (SettingNotFoundException $e) {
            $this->logger->debug('no setting found. Processing', ['exception' => $e]);
        }

        return new JsonResponse(['active' => null !== $setting], IResponse::OK);
    }

}