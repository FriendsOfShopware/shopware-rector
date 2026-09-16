<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Rule\BCChange\ValueObject\RemoveParameter;

final class ParameterRemovalRector extends AbstractBCChangeRector
{
    protected function supportedKind(): string
    {
        return self::REMOVE_PARAMETER;
    }

    protected function configurationType(): string
    {
        return RemoveParameter::class;
    }
}
