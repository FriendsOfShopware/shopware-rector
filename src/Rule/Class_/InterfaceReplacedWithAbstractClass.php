<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\Class_;

use PHPStan\Type\ObjectType;

class InterfaceReplacedWithAbstractClass
{
    public function __construct(protected string $interface, protected string $abstractClass)
    {
    }

    public function getInterface(): string
    {
        return $this->interface;
    }

    public function getAbstractClass(): string
    {
        return $this->abstractClass;
    }

    public function getInterfaceObject(): ObjectType
    {
        return new ObjectType($this->interface);
    }

    public function getAbstractClassObject(): ObjectType
    {
        return new ObjectType($this->abstractClass);
    }
}
