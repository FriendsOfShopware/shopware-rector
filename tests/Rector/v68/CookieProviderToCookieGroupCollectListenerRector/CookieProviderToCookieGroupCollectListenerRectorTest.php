<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector;

use Frosh\Rector\Rule\v68\CookieProviderToCookieGroupCollectListenerRector;
use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(CookieProviderToCookieGroupCollectListenerRector::class)]
final class CookieProviderToCookieGroupCollectListenerRectorTest extends AbstractShopware68RectorTestCase {}
