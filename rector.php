<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
    ])
    ->withSkip([
        __DIR__.'/app/Providers',
        __DIR__.'/app/Http/Middleware/HandleInertiaRequests.php',
        __DIR__.'/app/Http/Controllers/ProfileController.php',
    ])
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::PRIVATIZATION,
        SetList::NAMING,
    ]);
