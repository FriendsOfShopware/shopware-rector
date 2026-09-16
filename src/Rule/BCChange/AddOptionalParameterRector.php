<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Rule\BCChange\ValueObject\AddOptionalParameter;

final class AddOptionalParameterRector extends AbstractBCChangeRector
{
    protected function supportedKind(): string
    {
        return self::ADD_OPTIONAL_PARAMETER;
    }

    protected function configurationType(): string
    {
        return AddOptionalParameter::class;
    }
}
