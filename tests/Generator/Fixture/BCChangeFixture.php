<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator\Fixture;

use Frosh\Rector\Tests\Generator\Fixture\BCChange\NewOptionalParameter;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\NewRequiredParameter;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ParameterDefaultValueChange;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ParameterNameChange;
use Frosh\Rector\Tests\Generator\Fixture\BCChange\ParameterRemoval;
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

    #[ParameterNameChange(version: 'v6.8.0', parameterName: 'third', newName: 'renamed')]
    public function rename(string $required, bool $optional = false, ?string $third = null): void {}

    #[ParameterRemoval(version: 'v6.8.0', parameterName: 'obsolete')]
    public function remove(string $required, ?string $obsolete = null): void {}

    #[NewRequiredParameter(version: 'v6.8.0', parameterName: 'context', parameterType: 'object')]
    public function requireParameter(string $required): void {}
}
