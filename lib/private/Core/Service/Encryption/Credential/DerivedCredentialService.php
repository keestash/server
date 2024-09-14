<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\Core\Service\Encryption\Credential;

use DateTimeImmutable;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Core\DTO\Encryption\Credential\Credential;
use Keestash\Exception\Repository\Derivation\DerivationException;
use Keestash\Exception\Repository\Derivation\DerivationNotFoundException;
use Keestash\Exception\Repository\NoRowsFoundException;
use Keestash\Exception\User\UserException;
use KSP\Core\DTO\Derivation\IDerivation;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class CredentialService
 *
 * @package Keestash\Core\Service\Encryption\Credential
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class DerivedCredentialService implements ICredentialService {

    public function __construct(
        private readonly IDerivationRepository $derivationRepository
        , private readonly LoggerInterface     $logger
        , private readonly IDerivationService  $derivationService
    ) {
    }

    /**
     * @param IKeyHolder $keyHolder
     * @return ICredential
     * @throws DerivationException
     * @throws UserException
     */
    #[\Override]
    public function createCredential(IKeyHolder $keyHolder): ICredential {
        if (!($keyHolder instanceof IUser)) {
            throw new UserException('currently, we support users only');
        }
        try {
            $derivation = $this->derivationRepository->get($keyHolder);
            return $this->createCredentialHelper($keyHolder, $derivation);
        } catch (DerivationNotFoundException|NoRowsFoundException $e) {
            $this->logger->warning(
                'no derivation found for keyholder',
                [
                    'exception'   => $e
                    , 'keyholder' => $keyHolder->getId()
                    , 'class'     => $keyHolder::class
                ]
            );
            throw new DerivationException();
        }
    }

    /**
     * @param IKeyHolder $keyHolder
     * @return ICredential
     * @throws UserException
     */
    #[\Override]
    public function createCredentialFromDerivation(IKeyHolder $keyHolder): ICredential {
        $derivation = new Derivation(
            Uuid::uuid4()->toString()
            , $keyHolder
            , $this->derivationService->derive($keyHolder->getPassword())
            , new DateTimeImmutable()
        );
        return $this->createCredentialHelper($keyHolder, $derivation);
    }

    /**
     * @param IKeyHolder  $keyHolder
     * @param IDerivation $derivation
     * @return ICredential
     */
    private function createCredentialHelper(IKeyHolder $keyHolder, IDerivation $derivation): ICredential {
        $credential = new Credential();
        $credential->setKeyHolder($keyHolder);
        $credential->setSecret($derivation->getDerived());
        $credential->setCreateTs($keyHolder->getCreateTs());
        $credential->setId($keyHolder->getId());
        return $credential;
    }

}
