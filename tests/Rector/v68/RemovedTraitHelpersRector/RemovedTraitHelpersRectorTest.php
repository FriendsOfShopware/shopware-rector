<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\RemovedTraitHelpersRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\RemovedTraitHelpersRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(RemovedTraitHelpersRector::class)]
final class RemovedTraitHelpersRectorTest extends AbstractShopware68RectorTestCase {}
