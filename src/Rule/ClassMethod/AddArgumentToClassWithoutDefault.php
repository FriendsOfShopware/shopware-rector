<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassMethod;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class AddArgumentToClassWithoutDefault
{
    /**
     * @param Type|null $type
     */
    public function __construct(protected string $class, protected string $method, protected int $position, protected string $name, protected $type)
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

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
