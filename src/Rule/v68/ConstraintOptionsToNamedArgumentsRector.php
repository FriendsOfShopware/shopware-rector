<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\PhpParser\Node\Value\ValueResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConstraintOptionsToNamedArgumentsRector extends AbstractRector
{
    private const PARAMETERS = [
        'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique' => ['salesChannelContext', 'message'],
        'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches' => ['salesChannelContext', 'message'],
        'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerVatIdentification' => ['countryId', 'shouldCheck', 'message'],
        'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode' => ['caseSensitiveCheck', 'countryId', 'message', 'messageRequired'],
        'Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists' => ['entity', 'context', 'primaryProperty', 'criteria', 'message'],
        'Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityNotExists' => ['entity', 'context', 'primaryProperty', 'criteria', 'message'],
    ];

    public function __construct(private readonly ValueResolver $valueResolver) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace Shopware constraint options with named constructor arguments', [new CodeSample(
            "new CustomerEmailUnique(['salesChannelContext' => \$context]);",
            'new CustomerEmailUnique(salesChannelContext: $context);',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /** @param New_ $node */
    public function refactor(Node $node): ?Node
    {
        if (!$node->class instanceof Name) {
            return null;
        }

        $class = $this->getName($node->class);
        $parameters = self::PARAMETERS[$class] ?? null;
        if ($parameters === null) {
            return null;
        }

        $args = NamedArguments::resolve($node->args, ['options', ...$parameters]);
        if ($args === null || !isset($args['options'])) {
            return null;
        }

        $options = $args['options']->value;
        unset($args['options']);
        if ($this->valueResolver->isNull($options)) {
            $node->args = array_values($args);

            return $node;
        }

        // Mixing legacy options and explicit parameters has different precedence per constraint.
        if ($class === 'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode' && $args === [] && $this->getType($options)->isString()->yes()) {
            $node->args = [new Arg($options, name: new Identifier('countryId'))];

            return $node;
        }
        if (!$options instanceof Array_ || $args !== []) {
            return null;
        }

        foreach ($options->items as $item) {
            if ($item === null || !$item->key instanceof String_ || $item->unpack || $item->byRef) {
                return null;
            }

            $name = $item->key->value;
            if ($class === 'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches' && $name === 'context') {
                $name = 'salesChannelContext';
            }

            if (!in_array($name, $parameters, true) || isset($args[$name])) {
                return null;
            }

            $args[$name] = new Arg($item->value, name: new Identifier($name));
        }

        $node->args = array_values($args);

        return $node;
    }
}
