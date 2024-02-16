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

namespace KSP\Core\Service\Metric;

use Prometheus\RegistryInterface;

interface ICollectorService {

    public const DEFAULT_HISTOGRAM_BUCKETS = [
        0.005,
        0.01,
        0.025,
        0.05,
        0.075,
        0.1,
        0.25,
        0.5,
        0.75,
        1,
        2.5,
        5,
        7.5,
        10
    ];

    /**
     * Provides the namespace encompassing all metrics collected by Prometheus.
     *
     * @return string
     */
    public function getNamespace(): string;

    /**
     * Provides the prefix applicable to all metrics.
     *
     * @return string
     */
    public function getPrefix(): string;

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
     */
    public function addCounter(string $name, int $incrementBy = 1, array $labels = [], string $helpText = ''): void;

    /**
     * Utilizes the gauge metric type for tracking values that can both increase and decrease,
     * examples include monitoring current memory usage or the count of items in a queue.
     *
     * @param string $name     Name of the metric
     * @param float  $value    Value to set for the metric
     * @param array  $labels   Descriptive labels for additional detail on the metric
     * @param string $helpText Descriptive text providing assistance in understanding the metric
     */
    public function addGauge(string $name, float $value, array $labels = [], string $helpText = ''): void;

    /**
     * Employs the histogram metric type to count how often values are observed within certain predefined ranges or buckets.
     *
     * @param string  $name     Name of the metric
     * @param float   $value    The value to be measured
     * @param array   $labels   Descriptive labels for further detail on the metric
     * @param float[] $buckets  The ranges or buckets for clustering the values
     * @param string  $helpText Descriptive text aiding in the metric's understanding
     */
    public function addHistogram(string $name, float $value, array $labels = [], array $buckets = ICollectorService::DEFAULT_HISTOGRAM_BUCKETS, string $helpText = ''): void;

    /**
     * Retrieves an instance of the registry utilized for storing values.
     *
     * @return RegistryInterface
     */
    public function getRegistry(): RegistryInterface;

}
