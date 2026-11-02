<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\Arguments\RemoveMethodCallParamRector;

use Frosh\Rector\Tests\Rector\AbstractFroshRectorTestCase;
use Frosh\Rector\Rule\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RemoveMethodCallParamRector::class)]
final class RemoveMethodCallParamRectorTest extends AbstractFroshRectorTestCase {}
