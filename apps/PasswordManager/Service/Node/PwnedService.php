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

namespace KSA\PasswordManager\Service\Node;

use doganoo\PHPAlgorithms\Datastructure\Graph\Tree\BinarySearchTree;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;
use KSA\PasswordManager\Entity\Node\Pwned\Api\Passwords;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\CSV\ICSVService;
use KSP\Core\Service\Logger\ILogger;

class PwnedService {

    private ICSVService     $csvService;
    private ClientInterface $client;
    private IConfigService  $configService;
    private ILogger         $logger;

    public function __construct(
        ICSVService       $csvService
        , ClientInterface $client
        , IConfigService  $configService
        , ILogger         $logger
    ) {
        $this->csvService    = $csvService;
        $this->client        = $client;
        $this->configService = $configService;
        $this->logger        = $logger;
    }

    /**
     * @param string $account
     * @return array
     * @throws GuzzleException
     * @throws JsonException
     */
    public function importBreaches(string $account): array {

        $apiKey = $this->configService->getValue('hibp_api_key');

        if (null === $apiKey) {
            return [];
        }

        $this->logger->error(sprintf('https://haveibeenpwned.com/api/v3/breachedaccount/%s?truncateResponse=false', $account));
        $response = $this->client->request(
            RequestMethodInterface::METHOD_GET
            , sprintf('https://haveibeenpwned.com/api/v3/breachedaccount/%s?truncateResponse=false', $account)
            , [
                RequestOptions::HEADERS => [
                    'hibp-api-key' => $apiKey
                ]
            ]
        );

        return json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );
    }

    public function importPasswords(string $prefix): BinarySearchTree {

        $binarySearchTree = new BinarySearchTree();
        $apiKey           = $this->configService->getValue('hibp_api_key');

        if (null === $apiKey) {
            return $binarySearchTree;
        }

        $response = $this->client->request(
            RequestMethodInterface::METHOD_GET
            , sprintf('https://api.pwnedpasswords.com/range/%s', $prefix)
            , [
                RequestOptions::HEADERS => [
                    'hibp-api-key' => $apiKey
                ]
            ]
        );

        foreach ($this->csvService->readString((string) $response->getBody(), false) as $row) {
            $exploded = explode(":", $row[0]);
            $suffix   = $exploded[0];
            $count    = $exploded[1];

            $binarySearchTree->insertValue(
                new Passwords(
                    strtoupper($prefix)
                    , strtoupper($suffix)
                    , (int) $count
                )
            );
        }
        return $binarySearchTree;
    }

    public function generateSearchHash(string $string): string {
        $string = sha1($string);
        return substr($string, 0, 5);
    }

}