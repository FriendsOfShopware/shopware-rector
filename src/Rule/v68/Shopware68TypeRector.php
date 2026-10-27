<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class Shopware68TypeRector extends AbstractRector
{
    private const TYPES = [
        'Shopware\Core\Framework\Api\OAuth\AccessTokenRepository' => 'League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface',
        'Shopware\Core\Framework\Api\OAuth\RefreshTokenRepository' => 'League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface',
        'Shopware\Core\Framework\Api\OAuth\ScopeRepository' => 'League\OAuth2\Server\Repositories\ScopeRepositoryInterface',
        'Shopware\Core\Framework\Api\OAuth\UserRepository' => 'League\OAuth2\Server\Repositories\UserRepositoryInterface',
        'Shopware\Core\Framework\Api\OAuth\ClientRepository' => 'League\OAuth2\Server\Repositories\ClientRepositoryInterface',
        'Shopware\Core\Framework\Api\OAuth\Client\ApiClient' => 'League\OAuth2\Server\Entities\ClientEntityInterface',
        'Shopware\Core\Framework\Api\OAuth\AccessToken' => 'League\OAuth2\Server\Entities\AccessTokenEntityInterface',
        'Shopware\Core\Framework\Api\OAuth\RefreshToken' => 'League\OAuth2\Server\Entities\RefreshTokenEntityInterface',
        'Shopware\Core\Framework\Api\OAuth\User\User' => 'League\OAuth2\Server\Entities\UserEntityInterface',
        'Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface' => 'Shopware\Core\System\NumberRange\ValueGenerator\AbstractNumberRangeValueGenerator',
        'Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface' => 'Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use public interfaces and abstract services in native type declarations', [new CodeSample(
            'function token(AccessToken $token): AccessToken { return $token; }',
            'function token(AccessTokenEntityInterface $token): AccessTokenEntityInterface { return $token; }',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Param::class, Property::class, ClassMethod::class, Node\Stmt\Function_::class, Node\Expr\Closure::class, Node\Expr\ArrowFunction::class];
    }

    public function refactor(Node $node): ?Node
    {
        $type = $node instanceof Param || $node instanceof Property ? $node->type : ($node instanceof Node\FunctionLike ? $node->getReturnType() : null);
        if ($type === null) {
            return null;
        }
        $changed = false;
        $newType = $this->renameType($type, $changed);
        if (!$changed) {
            return null;
        }
        if ($node instanceof Param || $node instanceof Property) {
            $node->type = $newType;
        } elseif ($node instanceof ClassMethod || $node instanceof Node\Stmt\Function_ || $node instanceof Node\Expr\Closure || $node instanceof Node\Expr\ArrowFunction) {
            $node->returnType = $newType;
        }

        return $node;
    }

    private function renameType(Node\ComplexType|Node\Identifier|Name $type, bool &$changed): Node\ComplexType|Node\Identifier|Name
    {
        if ($type instanceof Name || $type instanceof Node\Identifier) {
            return $this->renameName($type, $changed);
        }
        if ($type instanceof Node\NullableType) {
            $type->type = $this->renameName($type->type, $changed);
        } elseif ($type instanceof Node\UnionType || $type instanceof Node\IntersectionType) {
            foreach ($type->types as $key => $inner) {
                if ($inner instanceof Node\IntersectionType) {
                    foreach ($inner->types as $index => $part) {
                        $inner->types[$index] = $this->renameName($part, $changed);
                    }
                    $inner->types = $this->uniqueTypes(array_values($inner->types));
                    if (count($inner->types) === 1) {
                        $type->types[$key] = $inner->types[0];
                    }
                } else {
                    $type->types[$key] = $this->renameName($inner, $changed);
                }
            }
            $type->types = $this->uniqueTypes(array_values($type->types));
            if (count($type->types) === 1) {
                return $type->types[0];
            }
        }

        return $type;
    }

    /**
     * @template T of Node
     *
     * @param list<T> $types
     *
     * @return list<T>
     */
    private function uniqueTypes(array $types): array
    {
        $unique = [];
        foreach ($types as $type) {
            foreach ($unique as $existing) {
                if ($this->nodeComparator->areNodesEqual($type, $existing)) {
                    continue 2;
                }
            }
            $unique[] = $type;
        }

        return $unique;
    }

    private function renameName(Node\Identifier|Name $type, bool &$changed): Node\Identifier|Name
    {
        if ($type instanceof Name) {
            $replacement = self::TYPES[$this->getName($type)] ?? null;
            if ($replacement !== null) {
                $changed = true;

                return new FullyQualified($replacement);
            }
        }

        return $type;
    }
}
