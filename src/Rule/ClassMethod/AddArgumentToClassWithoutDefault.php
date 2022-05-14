<?php

namespace Frosh\Rector\Rule\ClassMethod;

use PHPStan\Type\ObjectType;

class AddArgumentToClassWithoutDefault
{
    protected string $class;
    protected string $method;
    protected int $position;

    /**
     * @var \PHPStan\Type\Type|null
     */
    protected $type;
    protected string $name;

    public function __construct(string $class, string $method, int $position, string $name, $type)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->type = $type;
        $this->name = $name;
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

    public function getType(): ?\PHPStan\Type\Type
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
