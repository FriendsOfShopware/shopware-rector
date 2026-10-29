<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\ConstraintOptionsToNamedArgumentsRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\ConstraintOptionsToNamedArgumentsRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(ConstraintOptionsToNamedArgumentsRector::class)]
final class ConstraintOptionsToNamedArgumentsRectorTest extends AbstractShopware68RectorTestCase {}
