<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterName;

final class ParameterNameChangeRector extends AbstractBCChangeRector
{
    protected function supportedKind(): string
    {
        return self::RENAME_PARAMETER;
    }

    protected function configurationType(): string
    {
        return ChangeParameterName::class;
    }
}
