<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator\Fixture\BCChange;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class NewRequiredParameter
{
    public function __construct(
        public string $version,
        public string $parameterName,
        public string $parameterType,
    ) {}
}
