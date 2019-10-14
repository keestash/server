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

namespace Keestash\Core\Manager\HookManager;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use doganoo\PHPUtil\Log\FileLogger;
use Keestash\Core\Service\DateTimeService;
use KSP\Core\Backend\IBackend;
use KSP\Core\Manager\HookManager\IHookManager;
use KSP\Hook\IHook;

class HookManager implements IHookManager {

    /** @var ArrayList $preController */
    private $preController = null;
    /** @var ArrayList $postController */
    private $postController = null;

    public function __construct(
        ?IBackend $backend
        , ?DateTimeService $dateTimeService = null
    ) {
        $this->preController  = new ArrayList();
        $this->postController = new ArrayList();
    }

    public function addPre(IHook $hook): void {
        $this->preController->add($hook);
    }

    public function addPost(IHook $hook): void {
        $this->postController->add($hook);
    }

    public function executePre(...$parameters): bool {
        /** @var IHook $hook */
        foreach ($this->preController as $hook) {
            $hook->performAction($parameters);
        }
        return true;
    }

    public function executePost(...$parameters): bool {
        /** @var IHook $hook */
        foreach ($this->postController as $hook) {
            $hook->performAction($parameters);
        }
        return true;
    }

}