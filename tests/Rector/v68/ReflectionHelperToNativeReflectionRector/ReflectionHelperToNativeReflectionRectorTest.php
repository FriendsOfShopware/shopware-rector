<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\ReflectionHelperToNativeReflectionRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\ReflectionHelperToNativeReflectionRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(ReflectionHelperToNativeReflectionRector::class)]
final class ReflectionHelperToNativeReflectionRectorTest extends AbstractShopware68RectorTestCase {}
