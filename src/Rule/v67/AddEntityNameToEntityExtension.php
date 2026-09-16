<?php declare(strict_types=1);

namespace Frosh\Rector\Rule\v67;

use Frosh\Rector\Version\ShopwareVersionRange;
use Frosh\Rector\Version\VersionAwareRectorInterface;
use PhpParser\Modifiers;
use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddEntityNameToEntityExtension extends AbstractRector implements ConfigurableRectorInterface, VersionAwareRectorInterface
{
    private bool $removeDefinitionClass = false;

    public static function isActive(ShopwareVersionRange $versions): bool
    {
        return $versions->minimumIsAtLeast('6.5.0') && $versions->targetIsAtLeast('6.7.0');
    }

    public static function configuration(ShopwareVersionRange $versions): array
    {
        return ['minimumVersion' => $versions->minimum];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('NAME', [
            new CodeSample(
                <<<'PHP'
                    class Foo extends EntityExtension {
                        public function getDefinitionClass() {
                            return ProductDefinition::class;
                        }
                    }
                    PHP,
                <<<'PHP'
                    class Foo extends EntityExtension {
                        public function getDefinitionClass() {
                            return ProductDefinition::class;
                        }

                        public function getEntityName() {
                            return ProductDefinition::ENTITY_NAME;
                        }
                    }
                    PHP,
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\Class_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        if (!$this->isObjectType($node, new ObjectType('Shopware\Core\Framework\DataAbstractionLayer\EntityExtension'))) {
            return null;
        }

        $foundGetEntityName = false;
        $targetDefinitionClass = null;

        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Node\Stmt\ClassMethod) {
                continue;
            }

            if ($stmt->name->toString() === 'getEntityName') {
                $foundGetEntityName = true;
            }

            if ($stmt->name->toString() === 'getDefinitionClass' && $stmt->stmts !== null) {
                foreach ($stmt->stmts as $methodStmts) {
                    if ($methodStmts instanceof Node\Stmt\Return_) {
                        if ($methodStmts->expr instanceof Node\Expr\ClassConstFetch) {
                            $targetDefinitionClass = clone $methodStmts->expr;
                            $targetDefinitionClass->name = new Node\Identifier('ENTITY_NAME');
                        }
                    }
                }
            }
        }

        if ($this->removeDefinitionClass) {
            // remove getDefinitionClass method
            $node->stmts = array_values(array_filter($node->stmts, static function (Node\Stmt $stmt): bool {
                return !$stmt instanceof Node\Stmt\ClassMethod || $stmt->name->toString() !== 'getDefinitionClass';
            }));
        }

        if (!$foundGetEntityName) {
            $classMethod = new Node\Stmt\ClassMethod('getEntityName');
            $classMethod->returnType = new Node\Name('string');
            $classMethod->flags = Modifiers::PUBLIC;

            if ($targetDefinitionClass) {
                $classMethod->stmts[] = new Node\Stmt\Return_($targetDefinitionClass);
            } else {
                $classMethod->stmts[] = new Node\Stmt\Return_(new Node\Scalar\String_('COULD NOT FIND ENTITY NAME'));
            }

            $node->stmts[] = $classMethod;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $minimumVersion = (string) ($configuration['minimumVersion'] ?? '6.5.0');
        $this->removeDefinitionClass = version_compare($minimumVersion, '6.7.0', '>=');
    }
}
