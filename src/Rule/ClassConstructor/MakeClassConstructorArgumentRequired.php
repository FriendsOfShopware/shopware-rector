<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassConstructor;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class MakeClassConstructorArgumentRequired
{
    public function __construct(protected string $class, protected int $position, protected ?Type $default = null)
    {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getClassObject(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getDefault(): ?Type
    {
        return $this->default;
    }
}
