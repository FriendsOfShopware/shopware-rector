<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange\ValueObject;

final readonly class AddRequiredParameter extends MethodChange
{
    public function __construct(string $version, string $class, string $method, public int $position, public string $parameter, public string $type)
    {
        parent::__construct($version, $class, $method);
    }

    public function configuration(): array
    {
        return $this->methodConfiguration() + ['position' => $this->position, 'parameter' => $this->parameter, 'type' => $this->type];
    }
}
