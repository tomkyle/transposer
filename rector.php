<?php

use Rector\Core\Configuration\Option;
use Rector\ValueObject\PhpVersion;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codingStyle: true,
        codeQuality: true
    );
