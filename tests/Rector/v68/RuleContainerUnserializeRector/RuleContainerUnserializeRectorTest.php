<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\RuleContainerUnserializeRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\RuleContainerUnserializeRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(RuleContainerUnserializeRector::class)]
final class RuleContainerUnserializeRectorTest extends AbstractShopware68RectorTestCase {}
