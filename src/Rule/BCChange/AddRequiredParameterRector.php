<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Rule\BCChange\ValueObject\AddRequiredParameter;

final class AddRequiredParameterRector extends AbstractBCChangeRector
{
    protected function supportedKind(): string
    {
        return self::ADD_REQUIRED_PARAMETER;
    }

    protected function configurationType(): string
    {
        return AddRequiredParameter::class;
    }
}
