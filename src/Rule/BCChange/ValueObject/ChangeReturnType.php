<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange\ValueObject;

final readonly class ChangeReturnType extends MethodChange
{
    public function __construct(string $version, string $class, string $method, public ?string $currentType, public string $type)
    {
        parent::__construct($version, $class, $method);
    }

    public function configuration(): array
    {
        return $this->methodConfiguration() + ['currentType' => $this->currentType, 'type' => $this->type];
    }
}
