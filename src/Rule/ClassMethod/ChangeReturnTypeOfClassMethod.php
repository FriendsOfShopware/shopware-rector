<?php declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassMethod;

use PhpParser\Node\Name;

final class ChangeReturnTypeOfClassMethod
{
    public function __construct(public readonly string $class, public readonly string $method, public readonly Name $returnType) {}
}
