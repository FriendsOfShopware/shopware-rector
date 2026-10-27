<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\EntityDefinitionParentConstructorRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\EntityDefinitionParentConstructorRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(EntityDefinitionParentConstructorRector::class)]
final class EntityDefinitionParentConstructorRectorTest extends AbstractShopware68RectorTestCase {}
