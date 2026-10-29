<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\Shopware68ExceptionRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\Shopware68ExceptionRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(Shopware68ExceptionRector::class)]
final class Shopware68ExceptionRectorTest extends AbstractShopware68RectorTestCase {}
