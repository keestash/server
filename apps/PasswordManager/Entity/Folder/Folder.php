<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or
 * indirectly through a Keestash authorized reseller or distributor (a "Reseller"). Please read this EULA agreement
 * carefully before completing the installation process and using the Keestash software. It provides a license to use
 * the Keestash software and contains warranty information and liability disclaimers.
 */

namespace KSA\PasswordManager\Entity\Folder;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Node\Node;

class Folder extends Node {

    private ArrayList $edges;

    public function __construct() {
        $this->edges = new ArrayList();
        parent::__construct();
    }

    public function addEdge(Edge $node): void {
        $this->edges->add($node);
    }

    #[\Override]
    public function getType(): string {
        return Node::FOLDER;
    }

    #[\Override]
    public function getIcon(): string {
        return Node::ICON_FOLDER;
    }

    public function getEdges(): ArrayList {
        return $this->edges;
    }

    public function setEdges(ArrayList $edges): void {
        $this->edges = $edges;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return parent::jsonSerialize() + [
                "edges"       => $this->getEdges()
                , "edge_size" => $this->getEdges()->length()
            ];
    }

}
