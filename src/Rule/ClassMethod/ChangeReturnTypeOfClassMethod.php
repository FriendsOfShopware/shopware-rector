<?php declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassMethod;

class ChangeReturnTypeOfClassMethod
{
    public function __construct(public readonly string $class, public readonly string $method, public readonly string $returnType)
    {
    }
}
