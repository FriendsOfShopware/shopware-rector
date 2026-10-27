<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\PrimaryOrderAssociationRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\PrimaryOrderAssociationRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(PrimaryOrderAssociationRector::class)]
final class PrimaryOrderAssociationRectorTest extends AbstractShopware68RectorTestCase {}
