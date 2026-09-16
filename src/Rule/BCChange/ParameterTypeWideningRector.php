<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterType;

final class ParameterTypeWideningRector extends AbstractBCChangeRector
{
    protected function supportedKind(): string
    {
        return self::WIDEN_PARAMETER_TYPE;
    }

    protected function configurationType(): string
    {
        return ChangeParameterType::class;
    }
}
