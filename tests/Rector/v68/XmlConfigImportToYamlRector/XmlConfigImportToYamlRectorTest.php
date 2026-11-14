<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\XmlConfigImportToYamlRector;

use Frosh\Rector\Rule\v68\XmlConfigImportToYamlRector;
use Frosh\Rector\Tests\Rector\AbstractFroshRectorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(XmlConfigImportToYamlRector::class)]
final class XmlConfigImportToYamlRectorTest extends AbstractFroshRectorTestCase {}
