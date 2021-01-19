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
use KSP\Core\Cache\ICacheServer;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

class GetAll extends AbstractApi {

    private FrontendManager $frontendManager;
    private ICacheServer    $cacheServer;

    public function __construct(
        IL10N $l10n
        , FrontendManager $frontendManager
        , ICacheServer $cacheServer
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->frontendManager = $frontendManager;
        $this->cacheServer     = $cacheServer;
    }

    public function onCreate(array $parameters): void {
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );

        $redisKey = "frontendmanagerstrings";
        $data     = null;
        if (true === $this->cacheServer->exists($redisKey)) {
            $data = json_decode(
                $this->cacheServer->get($redisKey)
                , true
            );
        } else {
            $data = $this->hashTableToArray(
                $this->frontendManager->getAllRaw()
            );
            $this->cacheServer->set(
                $redisKey
                , json_encode($data)
            );
        }
        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "data" => $data
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
//            '';
        }
        return $array;
    }

    public function create(): void {

    }

    public function afterCreate(): void {

    }

}
