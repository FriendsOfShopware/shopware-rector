<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange\ValueObject;

final readonly class ChangeParameterType extends MethodChange
{
    public function __construct(string $version, string $class, string $method, public string $parameter, public ?string $currentType, public string $type)
    {
        parent::__construct($version, $class, $method);
    }

    public function configuration(): array
    {
        return $this->methodConfiguration() + ['parameter' => $this->parameter, 'currentType' => $this->currentType, 'type' => $this->type];
    }
}
