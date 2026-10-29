<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\FilterablePriceDefinitionRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\FilterablePriceDefinitionRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(FilterablePriceDefinitionRector::class)]
final class FilterablePriceDefinitionRectorTest extends AbstractShopware68RectorTestCase {}
