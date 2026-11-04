<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator\Fixture\BCChange;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class ClassMoved
{
    public function __construct(public string $version, public string $previousClassName) {}
}
