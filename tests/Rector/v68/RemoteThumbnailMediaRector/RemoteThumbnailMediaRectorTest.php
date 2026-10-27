<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\RemoteThumbnailMediaRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\RemoteThumbnailMediaRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(RemoteThumbnailMediaRector::class)]
final class RemoteThumbnailMediaRectorTest extends AbstractShopware68RectorTestCase {}
