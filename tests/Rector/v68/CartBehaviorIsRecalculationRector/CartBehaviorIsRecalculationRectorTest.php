<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CartBehaviorIsRecalculationRector;

use PHPUnit\Framework\Attributes\CoversClass;
use Frosh\Rector\Rule\v68\CartBehaviorIsRecalculationRector;
use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;

/**
 * @internal
 */
#[CoversClass(CartBehaviorIsRecalculationRector::class)]
final class CartBehaviorIsRecalculationRectorTest extends AbstractShopware68RectorTestCase {}
