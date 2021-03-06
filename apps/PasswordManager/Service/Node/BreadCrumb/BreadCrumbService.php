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

namespace KSA\PasswordManager\Service\Node\BreadCrumb;

use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\Cache\ICacheService;
use KSP\Core\DTO\User\IUser;
use KSP\L10N\IL10N;

class BreadCrumbService {

    private const ROOT_CACHE_KEY = 'key.cache.root.{userId}';

    private ICacheService  $cacheService;
    private NodeRepository $nodeRepository;
    private IL10N          $translator;

    public function __construct(
        ICacheService $cacheService
        , NodeRepository $nodeRepository
        , IL10N $translator
    ) {
        $this->cacheService   = $cacheService;
        $this->nodeRepository = $nodeRepository;
        $this->translator     = $translator;
    }

    public function getBreadCrumbs(Node $node, IUser $user): array {
        $root = json_decode(
            $this->getRootForUser($user)
            , true
        );

        $values = json_decode(
            $this->getPathToRoot($node)
            , true
        );

        $values[] = [
            'id'     => $root['id']
            , 'name' => $this->translator->translate("Home")
        ];
        return array_reverse($values);
    }

    private function getPathToRoot(Node $node): string {
        $cacheKey = 'key.cache.node.' . $node->getId();

        if ($this->cacheService->exists($cacheKey)) {
            return $this->cacheService->get($cacheKey);
        }

        $nodes = $this->nodeRepository->getPathToRoot($node);

        $values = [];
        foreach ($nodes as $node) {
            $values[] = [
                'id'     => $node['id']
                , 'name' => $node['name']
            ];
        }

        $values = json_encode($values);
        $this->cacheService->set($cacheKey, $values);
        return (string) $values;
    }

    private function getRootForUser(IUser $user): string {
        $cacheKey = str_replace("{userId}", (string) $user->getId(), BreadCrumbService::ROOT_CACHE_KEY);
        if ($this->cacheService->exists($cacheKey)) {
            return $this->cacheService->get($cacheKey);
        }
        $root    = $this->nodeRepository->getRootForUser($user, 0, 1);
        $encoded = json_encode(
            [
                'id'        => $root->getId()
                , 'name'    => $root->getName()
                , 'is_root' => true
            ]
        );
        $this->cacheService->set($cacheKey, $encoded);
        return (string) $encoded;
    }

}