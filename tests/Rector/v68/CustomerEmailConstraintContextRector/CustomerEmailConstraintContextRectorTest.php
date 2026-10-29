<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CustomerEmailConstraintContextRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\CustomerEmailConstraintContextRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(CustomerEmailConstraintContextRector::class)]
final class CustomerEmailConstraintContextRectorTest extends AbstractShopware68RectorTestCase {}
