<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator\Fixture;

use Frosh\Rector\Tests\Generator\Fixture\BCChange\NewOptionalParameter;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ParameterDefaultValueChange;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ParameterTypeWidening;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ReturnTypeNarrowing;

final class BCChangeFixture
{
    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'fresh', parameterType: 'bool', defaultValue: false)]
    #[ParameterTypeWidening(version: 'v6.8.0', parameterName: 'id', newType: 'int|string')]
    #[ReturnTypeNarrowing(version: 'v6.8.0', newType: 'static')]
    public function load(string $id): object
    {
        return $this;
    }

    #[ParameterDefaultValueChange(version: 'v6.8.0', parameterName: 'enabled', newDefaultValue: true)]
    public function changeDefault(bool $enabled = false): void {}
}
