<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ReflectionHelperToNativeReflectionRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace the removed ReflectionHelper with native reflection', [new CodeSample(
            "ReflectionHelper::getProperty(MyClass::class, 'name');",
            "new \\ReflectionProperty(MyClass::class, 'name');",
        )]);
    }

    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /** @param StaticCall $node */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->class, 'Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper')) {
            return null;
        }

        $method = $this->getName($node->name);
        $parameters = match ($method) {
            'getMethod' => ['className', 'methodName'],
            'getProperty' => ['className', 'propertyName'],
            'getPropertyValue' => ['object', 'propertyName'],
            'getFileName' => ['className'],
            default => null,
        };
        if ($parameters === null) {
            return null;
        }

        $args = NamedArguments::resolve($node->args, $parameters);
        if ($args === null || count($args) !== count($parameters)) {
            return null;
        }

        // Native reflection uses different parameter names.
        foreach ($args as $name => $arg) {
            $arg->name = new Node\Identifier(in_array($name, ['className', 'object'], true) ? 'objectOrClass' : ($name === 'methodName' ? 'method' : 'property'));
        }

        if ($method === 'getFileName') {
            $args['className']->name = new Node\Identifier('objectOrClass');

            return new MethodCall(new New_(new FullyQualified('ReflectionClass'), array_values($args)), 'getFileName');
        }

        if ($method === 'getPropertyValue') {
            // Do not evaluate an object-producing call twice.
            if (!$args['object']->value instanceof Variable) {
                return null;
            }

            return new MethodCall(new New_(new FullyQualified('ReflectionProperty'), array_values($args)), 'getValue', [new Arg($args['object']->value)]);
        }

        return new New_(new FullyQualified($method === 'getMethod' ? 'ReflectionMethod' : 'ReflectionProperty'), array_values($args));
    }
}
