<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v67\AddLoggerToScheduledTaskConstructorRector;

use PHPUnit\Framework\Attributes\CoversClass;
use Frosh\Rector\Rule\v67\AddLoggerToScheduledTaskConstructorRector;
use Frosh\Rector\Tests\Rector\AbstractFroshRectorTestCase;

/**
 * @internal
 */
#[CoversClass(AddLoggerToScheduledTaskConstructorRector::class)]
class AddLoggerToScheduledTaskConstructorRectorTest extends AbstractFroshRectorTestCase {}
