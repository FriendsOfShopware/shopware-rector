<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\TranslationLoaderLocaleRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\TranslationLoaderLocaleRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(TranslationLoaderLocaleRector::class)]
final class TranslationLoaderLocaleRectorTest extends AbstractShopware68RectorTestCase {}
