<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\MailFlowCriteriaEventRector;

use Frosh\Rector\Tests\Rector\v68\AbstractShopware68RectorTestCase;
use Frosh\Rector\Rule\v68\MailFlowCriteriaEventRector;
use PHPUnit\Framework\Attributes\CoversClass;

/** @internal */
#[CoversClass(MailFlowCriteriaEventRector::class)]
final class MailFlowCriteriaEventRectorTest extends AbstractShopware68RectorTestCase {}
