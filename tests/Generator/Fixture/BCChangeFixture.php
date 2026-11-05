<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator\Fixture;

use Frosh\Rector\Tests\Generator\Fixture\BCChange\NewOptionalParameter;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ClassMoved;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ParameterDefaultValueChange;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ParameterTypeWidening;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ReturnTypeNarrowing;

#[ClassMoved('v6.8.0', 'Legacy\Fixture')]
#[ClassMoved(version: 'v6.9.0', previousClassName: 'Future\Fixture')]
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

    #[ReturnTypeNarrowing(version: 'v6.9.0', newType: 'static')]
    public function future(): object
    {
        return $this;
    }
}
