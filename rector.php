<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/apps',
        __DIR__ . '/config',
        __DIR__ . '/cron',
        __DIR__ . '/lib',
        __DIR__ . '/public',
        __DIR__ . '/test',
    ])
    // uncomment to reach your current PHP version
     ->withPhpSets(php83: true)
    ->withTypeCoverageLevel(0);
