<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassConstructor;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class MakeClassConstructorArgumentRequired
{
    protected string $class;
    protected int $position;
    protected ?Type $default;
    protected bool $removeNullableForArgument;

    public function __construct(string $class, int $position, ?Type $default = null, bool $removeNullableForArgument = false)
    {
        $this->class = $class;
        $this->position = $position;
        $this->default = $default;
        $this->removeNullableForArgument = $removeNullableForArgument;
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

    public function shouldNullableForArgumentBeRemoved(): bool
    {
        return $this->removeNullableForArgument;
    }
}
