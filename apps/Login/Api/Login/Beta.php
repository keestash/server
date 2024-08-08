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

namespace KSA\Login\Api\Login;

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Service\Router\VerificationService;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\Token\TokenNotCreatedException;
use Keestash\Exception\User\UserNotFoundException;
use KSA\Login\Entity\IResponseCodes;
use KSA\Login\Service\TokenService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\LDAP\IConnectionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\LDAP\ILDAPService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final readonly class Beta {

    public function __construct(
        private Alpha $alpha
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws TokenNotCreatedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        return $this->alpha->handle($request);
    }

}