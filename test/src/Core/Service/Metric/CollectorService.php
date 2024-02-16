<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

namespace KST\Service\Core\Service\Metric;

use KSP\Core\Service\Metric\ICollectorService;
use Prometheus\RegistryInterface;

final readonly class CollectorService implements ICollectorService {

    public function __construct(
        private RegistryInterface $registry
    ) {
    }

    #[\Override]
    public function getNamespace(): string {
        return 'keestashtest';
    }

    #[\Override]
    public function getPrefix(): string {
        return 'keestashtestprefix';
    }

    #[\Override]
    public function addCounter(string $name, int $incrementBy = 1, array $labels = [], string $helpText = ''): void {
        // silence is golden
    }

    #[\Override]
    public function addGauge(string $name, float $value, array $labels = [], string $helpText = ''): void {
        // silence is golden
    }

    #[\Override]
    public function addHistogram(string $name, float $value, array $labels = [], array $buckets = ICollectorService::DEFAULT_HISTOGRAM_BUCKETS, string $helpText = ''): void {
        // silence is golden
    }

    #[\Override]
    public function getRegistry(): RegistryInterface {
        return $this->registry;
    }

}
