<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class Shopware68PropertyReadRector extends AbstractRector
{
    private const GETTERS = [
        'Shopware\Core\Content\ImportExport\ImportExportProfileEntity' => ['label' => 'getTechnicalName'],
        'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique' => ['message' => 'getMessage'],
        'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches' => ['message' => 'getMessage'],
        'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerVatIdentification' => ['message' => 'getMessage'],
        'Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode' => ['caseSensitiveCheck' => 'isCaseSensitiveCheck', 'countryId' => 'getCountryId'],
        'Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists' => ['message' => 'getMessage'],
        'Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityNotExists' => ['message' => 'getMessage'],
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Read constraint properties through public getters', [new CodeSample(
            '$constraint->message;',
            '$constraint->getMessage();',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [PropertyFetch::class, NullsafePropertyFetch::class];
    }

    /** @param PropertyFetch|NullsafePropertyFetch $node */
    public function refactor(Node $node): ?Node
    {
        foreach ([AttributeKey::IS_BEING_ASSIGNED, AttributeKey::IS_ASSIGN_OP_VAR, AttributeKey::IS_ASSIGN_REF_EXPR, AttributeKey::IS_BYREF_VAR, AttributeKey::IS_UNSET_VAR, AttributeKey::IS_ISSET_VAR] as $attribute) {
            if ($node->getAttribute($attribute)) {
                return null;
            }
        }
        foreach (self::GETTERS as $class => $properties) {
            $method = $properties[$this->getName($node->name)] ?? null;
            if ($method !== null && $this->isObjectType($node->var, new ObjectType($class))) {
                return $node instanceof NullsafePropertyFetch ? new NullsafeMethodCall($node->var, $method) : new MethodCall($node->var, $method);
            }
        }

        return null;
    }
}
