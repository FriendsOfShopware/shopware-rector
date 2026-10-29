<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\Shopware68SignatureRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\Shopware68SignatureRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(Shopware68SignatureRector::class)]
final class Shopware68SignatureRectorTest extends AbstractShopware68RectorTestCase {}
