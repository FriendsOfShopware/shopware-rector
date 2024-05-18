<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassConstructor;

use PHPStan\Type\ObjectType;

class RemoveArgumentFromClassConstruct
{
    public function __construct(protected string $class, protected int $position)
    {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
