<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange\ValueObject;

final readonly class ChangeParameterName extends MethodChange
{
    /** @param list<array{name: string, hasDefault: bool, default?: mixed}> $parametersBefore */
    public function __construct(string $version, string $class, string $method, public int $position, public string $parameter, public string $newName, public array $parametersBefore)
    {
        parent::__construct($version, $class, $method);
    }

    public function configuration(): array
    {
        return $this->methodConfiguration() + ['position' => $this->position, 'parameter' => $this->parameter, 'newName' => $this->newName, 'parametersBefore' => $this->parametersBefore];
    }
}
