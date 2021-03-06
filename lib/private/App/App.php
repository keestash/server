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
    private string $id;
    private int    $order          = 0;
    private string $name;
    private string $namespace;
    private string $appPath;
    private string $templatePath;
    private string $stringPath;
    private string $faIconClass;
    private string $baseRoot;
    private int    $version;
    private string $versionString;
    private string $type;
    private bool   $showIcon;
    private array  $backgroundJobs = [];
    private array  $settings       = [];
    private array  $styleSheets    = [];
    private bool   $demonstratable;

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

    public function getStringPath(): string {
        return (string) $this->stringPath;
    }

    public function setStringPath(string $stringPath): void {
        $this->stringPath = $stringPath;
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

    public function getBackgroundJobs(): array {
        return $this->backgroundJobs;
    }

    public function setBackgroundJobs(array $backgroundJobs): void {
        $this->backgroundJobs = $backgroundJobs;
    }

    public function getSettings(): array {
        return $this->settings;
    }

    public function setSettings(array $settings): void {
        $this->settings = $settings;
    }

    public function getStyleSheets(): array {
        return $this->styleSheets;
    }

    public function setStyleSheets(array $styleSheets): void {
        $this->styleSheets = $styleSheets;
    }

    /**
     * @return string
     */
    public function getBaseRoot(): string {
        return $this->baseRoot;
    }

    /**
     * @param string $baseRoot
     */
    public function setBaseRoot(string $baseRoot): void {
        $this->baseRoot = $baseRoot;
    }

    /**
     * @return bool
     */
    public function isDemonstrateable(): bool {
        return $this->demonstratable;
    }

    /**
     * @param bool $demonstratable
     */
    public function setDemonstratable(bool $demonstratable): void {
        $this->demonstratable = $demonstratable;
    }

}
