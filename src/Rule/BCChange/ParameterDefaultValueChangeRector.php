<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterDefault;

final class ParameterDefaultValueChangeRector extends AbstractBCChangeRector
{
    protected function supportedKind(): string
    {
        return self::EXPLICIT_CURRENT_DEFAULT;
    }

    protected function configurationType(): string
    {
        return ChangeParameterDefault::class;
    }
}
