<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;

/** @internal */
final class NamedArguments
{
    /**
     * Keep evaluation order, and refuse calls whose argument layout is unknown.
     *
     * @param array<\PhpParser\Node> $args
     * @param list<string> $parameters
     *
     * @return array<string, Arg>|null
     */
    public static function resolve(array $args, array $parameters): ?array
    {
        $resolved = [];
        foreach ($args as $position => $arg) {
            if (!$arg instanceof Arg || $arg->unpack || $arg->byRef) {
                return null;
            }

            $name = $arg->name?->toString() ?? $parameters[$position] ?? null;
            if ($name === null || !in_array($name, $parameters, true) || isset($resolved[$name])) {
                return null;
            }

            $copy = clone $arg;
            $copy->name = new Identifier($name);
            $resolved[$name] = $copy;
        }

        return $resolved;
    }
}
