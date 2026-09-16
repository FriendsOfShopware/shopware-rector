<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Rule\BCChange\ValueObject\ChangeReturnType;

final class ReturnTypeNarrowingRector extends AbstractBCChangeRector
{
    protected function supportedKind(): string
    {
        return self::NARROW_RETURN_TYPE;
    }

    protected function configurationType(): string
    {
        return ChangeReturnType::class;
    }
}
