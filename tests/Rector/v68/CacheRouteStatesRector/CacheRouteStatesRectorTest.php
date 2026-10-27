<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CacheRouteStatesRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\CacheRouteStatesRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(CacheRouteStatesRector::class)]
final class CacheRouteStatesRectorTest extends AbstractShopware68RectorTestCase {}
