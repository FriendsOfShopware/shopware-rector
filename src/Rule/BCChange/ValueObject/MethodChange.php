<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange\ValueObject;

abstract readonly class MethodChange implements BCChange
{
    public function __construct(
        public string $version,
        public string $class,
        public string $method,
    ) {}

    public function version(): string
    {
        return $this->version;
    }

    /** @return array{version: string, class: string, method: string} */
    protected function methodConfiguration(): array
    {
        return ['version' => $this->version, 'class' => $this->class, 'method' => $this->method];
    }
}
