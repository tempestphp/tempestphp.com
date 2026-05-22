<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSets([TempestSetList::TEMPEST_310])
    // ->withPhpSets()
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
