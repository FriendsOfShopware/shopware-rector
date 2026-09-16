<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange\ValueObject;

final readonly class ChangeParameterDefault extends MethodChange
{
    public function __construct(string $version, string $class, string $method, public int $position, public string $parameter, public mixed $default)
    {
        parent::__construct($version, $class, $method);
    }

    public function configuration(): array
    {
        return $this->methodConfiguration() + ['position' => $this->position, 'parameter' => $this->parameter, 'default' => $this->default];
    }
}
