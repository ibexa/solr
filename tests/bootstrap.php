<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
use Ibexa\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase;
use Ibexa\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder;
use Ibexa\Core\Repository\ContentService;
use Symfony\Bridge\PhpUnit\ClockMock;

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies using composer to run the test suite.');
}

// Register ClockMock, as otherwise they are mocked until first method call.
// Those Mocks are needed for core integration setup.

ClockMock::register(DoctrineDatabase::class);
ClockMock::register(ContentService::class);
ClockMock::register(QueryBuilder::class);

$autoload = require_once $file;
