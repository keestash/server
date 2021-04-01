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

use Keestash\Api\AbstractApi;

use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;
use libphonenumber\ValidationResult;

/**
 * Class GetByName
 * @package KSA\PasswordManager\Api\Node
 */
class GetByName extends AbstractApi {

    private NodeRepository $nodeRepository;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);
        $this->nodeRepository = $nodeRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $name = $this->getParameter('name');

        if (null === $name) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "no username"
                ]
            );
            return;
        }

        $list = $this->nodeRepository->getByName($name, 0 ,1);

        /** @var Node $node */
        foreach ($list as $key => $node) {
            if ($node->getUser()->getId() !== $this->getToken()->getUser()->getId()) {
                $list->remove($key);
            }
        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $list
            ]
        );
    }

    public function afterCreate(): void {

    }

}