<?php
declare(strict_types=1);
/**
 * Keestash
 * Copyright (C) 2019 Dogan Ucar <dogan@dogan-ucar.de>
 *
 * End-User License Agreement (EULA) of Keestash
 * This End-User License Agreement ("EULA") is a legal agreement between you and Keestash
 * This EULA agreement governs your acquisition and use of our Keestash software ("Software") directly from Keestash or indirectly through a Keestash authorized reseller or distributor (a "Reseller").
 * Please read this EULA agreement carefully before completing the installation process and using the Keestash software. It provides a license to use the Keestash software and contains warranty information and liability disclaimers.
 */

namespace Keestash\App\Config;

use DateTimeInterface;
use doganoo\Backgrounder\BackgroundJob\JobList;
use KSP\App\Config\IApp;

class App implements IApp {

    private string            $id;
    private bool              $enabled;
    private int               $version;
    private DateTimeInterface $createTs;
    private JobList           $jobs;

    public function __construct() {
        $this->jobs = new JobList();
    }

    public function getId(): string {
        return $this->id;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function getVersion(): int {
        return $this->version;
    }

    public function setVersion(int $version): void {
        $this->version = $version;
    }

    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

    public function getBackgroundJobs(): JobList {
        return $this->jobs;
    }

    public function setBackgroundJobs(JobList $jobs): void {
        $this->jobs = $jobs;
    }

    public function jsonSerialize(): array {
        return [
            'id'          => $this->getId()
            , 'enabled'   => $this->isEnabled()
            , 'version'   => $this->getVersion()
            , 'create_ts' => $this->getCreateTs()
            , 'jobs'      => $this->getBackgroundJobs()
        ];
    }

}
