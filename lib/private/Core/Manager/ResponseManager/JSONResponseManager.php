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

namespace Keestash\Core\Manager\ResponseManager;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use Keestash\Core\Service\DateTimeService;
use KSP\Api\IResponse;
use KSP\Core\Backend\IBackend;
use KSP\Core\Manager\ResponseManager\IResponseManager;

class JSONResponseManager implements IResponseManager {

    public $responses = null;

    public function __construct(?IBackend $backend, ?DateTimeService $dateTimeService = null) {
        $this->responses = new ArrayList();
    }

    public function add(IResponse $response): void {
        $this->responses->add($response);
    }

    public function getResponses(): ArrayList {
        return $this->responses;
    }

    public function unsetResponses(): bool {
        $this->responses = new ArrayList();
        return true;
    }

}