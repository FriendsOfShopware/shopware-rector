<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange\ValueObject;

interface BCChange
{
    public function version(): string;

    /** @return array<string, mixed> */
    public function configuration(): array;
}
