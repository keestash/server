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

namespace KSA\PasswordManager\Service\Node\Edge;

use DateTime;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Node;

class EdgeService {

    public function prepareRegularEdge(Node $node, Node $parent): Edge {
        return $this->prepareEdge($node, $parent, Edge::TYPE_REGULAR);
    }

    private function prepareEdge(Node $node, Node $parent, string $type): Edge {
        $edge = new Edge();
        $edge->setNode($node);
        $edge->setParent($parent);
        $edge->setType($type);
        $edge->setExpireTs(null);
        $edge->setOwner($node->getUser());
        $edge->setCreateTs(new DateTime());
        return $edge;
    }

    public function prepareEdgeForOrganization(Node $node, Node $parent): Edge {
        return $this->prepareEdge($node, $parent, Edge::TYPE_ORGANIZATION);
    }

}
