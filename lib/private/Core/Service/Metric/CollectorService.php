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

namespace Keestash\Core\Service\Metric;

use KSP\Core\Service\Metric\ICollectorService;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\RegistryInterface;

final readonly class CollectorService implements ICollectorService {

    public function __construct(
        private RegistryInterface $registry,
        private string            $namespace = '',
        private string            $prefix = ''
    ) {
    }

    /**
     * Incorporates a Counter type metric.
     *
     * A Counter is a metric that can only increment, never decrement, suitable for tracking counts of events like requests or errors.
     * (To track metrics that can decrease, use the addGauge method instead)
     *
     * @param string $name        Name of the metric
     * @param int    $incrementBy Amount to increase the metric by
     * @param array  $labels      Descriptive labels for the metric for detailed categorization
     * @param string $helpText    Descriptive text to assist understanding of the metric
     * @throws MetricsRegistrationException
     */
    public function addCounter(
        string $name,
        int    $incrementBy = 1,
        array  $labels = [],
        string $helpText = ''
    ): void {
        $this->getRegistry()
            ->getOrRegisterCounter(
                $this->getNamespace(),
                $this->getPrefix() . $name,
                $helpText,
                array_keys($labels)
            )
            ->incBy(
                $incrementBy,
                array_values($labels)
            );
    }

    /**
     * Utilizes the gauge metric type for tracking values that can both increase and decrease,
     * examples include monitoring current memory usage or the count of items in a queue.
     *
     * @param string $name     Name of the metric
     * @param float  $value    Value to set for the metric
     * @param array  $labels   Descriptive labels for additional detail on the metric
     * @param string $helpText Descriptive text providing assistance in understanding the metric
     * @throws MetricsRegistrationException
     */
    public function addGauge(
        string $name,
        float  $value,
        array  $labels = [],
        string $helpText = ''
    ): void {
        $this->getRegistry()
            ->getOrRegisterGauge(
                $this->getNamespace(),
                $this->getPrefix() . $name,
                $helpText,
                array_keys($labels)
            )
            ->set(
                $value,
                array_values($labels)
            );
    }

    /**
     * Employs the histogram metric type to count how often values are observed within certain predefined ranges or buckets.
     *
     * @param string  $name     Name of the metric
     * @param float   $value    The value to be measured
     * @param array   $labels   Descriptive labels for further detail on the metric
     * @param float[] $buckets  The ranges or buckets for clustering the values
     * @param string  $helpText Descriptive text aiding in the metric's understanding
     * @throws MetricsRegistrationException
     */
    public function addHistogram(
        string $name,
        float  $value,
        array  $labels = [],
        array  $buckets = ICollectorService::HISTOGRAM_BUCKETS,
        string $helpText = ''
    ): void {
        $this->getRegistry()
            ->getOrRegisterHistogram(
                $this->getNamespace(),
                $this->getPrefix() . $name,
                $helpText,
                array_keys($labels),
                $buckets
            )
            ->observe(
                $value,
                array_values($labels)
            );
    }

    /**
     * Retrieves an instance of the registry utilized for storing values.
     *
     * @return RegistryInterface
     */
    public function getRegistry(): RegistryInterface {
        return $this->registry;
    }


    /**
     * Provides the namespace encompassing all metrics collected by Prometheus.
     *
     * @return string
     */
    public function getNamespace(): string {
        return $this->namespace;
    }

    /**
     * Provides the prefix applicable to all metrics.
     *
     * @return string
     */
    public function getPrefix(): string {
        return $this->prefix;
    }

}
