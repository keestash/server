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

namespace Keestash\App;

use KSP\App\IApp;

class App implements IApp {

    public const TYPE_APP = "app";
    private $id            = null;
    private $order         = 0;
    private $name          = null;
    private $namespace     = null;
    private $appPath       = null;
    private $templatePath  = null;
    private $faIconClass   = null;
    private $baseRoot      = null;
    private $version       = null;
    private $versionString = null;
    private $type          = null;
    private $showIcon      = null;

    public function getOrder(): int {
        return $this->order;
    }

    public function setOrder(int $order): void {
        $this->order = $order;
    }

    public function getId(): string {
        return (string) $this->id;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return (string) $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getNamespace(): string {
        return (string) $this->namespace;
    }

    public function setNamespace(string $namespace): void {
        $this->namespace = $namespace;
    }

    public function getAppPath(): string {
        return (string) $this->appPath;
    }

    public function setAppPath(string $appPath): void {
        $this->appPath = $appPath;
    }

    public function getTemplatePath(): string {
        return (string) $this->templatePath;
    }

    public function setTemplatePath(string $templatePath): void {
        $this->templatePath = $templatePath;
    }

    public function getFAIconClass(): string {
        return (string) $this->faIconClass;
    }

    public function setFAIconClass(string $faIconClass): void {
        $this->faIconClass = $faIconClass;
    }

    public function getBaseRoute(): string {
        return (string) $this->baseRoot;
    }

    public function setBaseRoute(string $baseRoute): void {
        $this->baseRoot = $baseRoute;
    }

    public function setVersion(int $version): void {
        $this->version = $version;
    }

    public function getVersion(): int {
        return $this->version;
    }

    public function setVersionString(string $versionString): void {
        $this->versionString = $versionString;
    }

    public function getVersionString(): string {
        return $this->versionString;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setShowIcon(bool $showIcon): void {
        $this->showIcon = $showIcon;
    }

    public function showIcon(): bool {
        return $this->showIcon;
    }

}