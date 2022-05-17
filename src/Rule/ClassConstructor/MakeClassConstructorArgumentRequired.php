<?php

namespace Frosh\Rector\Rule\ClassConstructor;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class MakeClassConstructorArgumentRequired
{
    protected string $class;
    protected int $position;
    protected ?Type $default;

    public function __construct(string $class, int $position, ?Type $default = null)
    {
        $this->class = $class;
        $this->position = $position;
        $this->default = $default;
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