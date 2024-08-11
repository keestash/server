<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\Login\Event;

use DateTimeInterface;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\Event\ApplicationEndedEvent;
use KSP\Api\IRequest;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Service\Event\Listener\IListener;
use KSP\Core\Service\Metric\ICollectorService;
use KSP\Core\Service\Router\ApiLogServiceInterface;
use Laminas\Config\Config;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class ApplicationEndedEventListener implements IListener {

    public function __construct(
        private LoggerInterface        $logger,
        private ApiLogServiceInterface $apiRequestService,
        private Config                 $config,
        private ICollectorService      $collectorService
    ) {
    }

    public function execute(IEvent $event): void {
        if (false === ($event instanceof ApplicationEndedEvent)) {
            $this->logger->warning('listening to incorrect event', ['expected' => ApplicationEndedEvent::class, 'current' => $event::class]);
            return;
        }
        $this->logger->debug('start adding async post request data');
        $this->addToHistogram($event->getRequest());
        $this->apiRequestService->log($event->getRequest());
        $this->logger->debug('end adding async post request data');
    }

    private function addToHistogram(ServerRequestInterface $request): void {
        $path               = $request->getAttribute(IRequest::ATTRIBUTE_NAME_MATCHED_PATH);
        $metricDisallowList = $this->config->get(ConfigProvider::METRIC_DISALLOW_LIST, new Config([]))->toArray();

        if (true === in_array($path, $metricDisallowList, true)) {
            return;
        }

        /** @var DateTimeInterface $start */
        $start = $request->getAttribute(IRequest::ATTRIBUTE_NAME_APPLICATION_START);
        /** @var DateTimeInterface $end */
        $end = $request->getAttribute(IRequest::ATTRIBUTE_NAME_APPLICATION_END);

        $this->collectorService->addHistogram(
            'api_performance',
            $end->getTimestamp() - $start->getTimestamp(),
            [
                'path' => $path
            ]
        );

    }

}