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

namespace KSA\GeneralApi\Api\Template;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Api\AbstractApi;
use Keestash\Core\Manager\TemplateManager\FrontendManager;
use Keestash\Core\Permission\PermissionFactory;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\L10N\IL10N;

class GetAll extends AbstractApi {

    private $frontendManager = null;

    public function __construct(
        IL10N $l10n
        , FrontendManager $frontendManager
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->frontendManager = $frontendManager;
    }

    public function onCreate(array $parameters): void {
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "data" => $this->hashTableToArray(
                    $this->frontendManager->getAllRaw()
                )
            ]
        );
    }

    private function hashTableToArray(HashTable $table): array {
        $array = [];
        foreach ($table->keySet() as $key) {
            $name         = basename($key);
            $name         = str_replace(".twig", "", $name);
            $array[$name] =
                ($table->get($key));
        }
        return $array;
    }

    public function create(): void {

    }

    public function afterCreate(): void {

    }

}
