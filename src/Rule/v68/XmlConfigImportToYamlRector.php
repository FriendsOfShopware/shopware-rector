<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class XmlConfigImportToYamlRector extends AbstractRector
{
    private const CONFIGURATORS = [
        'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
        'Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Import the YAML files of XML service and route configuration, as Shopware 6.8 no longer loads XML. shopware-cli extension fix converts the XML files.',
            [
                new CodeSample(
                    <<<'PHP'
                        $container->import('services/subscriber.xml');
                        PHP,
                    <<<'PHP'
                        $container->import('services/subscriber.yaml');
                        PHP,
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /** @param MethodCall $node */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'import') || $node->isFirstClassCallable() || !$this->isConfigurator($node->var)) {
            return null;
        }

        $args = $node->getArgs();
        $resource = isset($args[0]) ? $this->yamlResource($args[0]->value) : null;
        if ($resource === null) {
            return null;
        }

        $args[0]->value = $resource;

        $type = $args[1]->value ?? null;
        if ($type instanceof String_ && $type->value === 'xml') {
            $args[1]->value = new String_('yaml');
        }

        return $node;
    }

    private function isConfigurator(Node\Expr $expr): bool
    {
        foreach (self::CONFIGURATORS as $configurator) {
            if ($this->isObjectType($expr, new ObjectType($configurator))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the resource with the .xml file name replaced by .yaml, also at
     * the end of a path like __DIR__ . '/services.xml', or null for other resources.
     */
    private function yamlResource(Node\Expr $resource): ?Node\Expr
    {
        if ($resource instanceof Concat) {
            $right = $this->yamlResource($resource->right);

            return $right === null ? null : new Concat($resource->left, $right);
        }

        if ($resource instanceof String_ && str_ends_with($resource->value, '.xml')) {
            return new String_(substr($resource->value, 0, -4) . '.yaml');
        }

        return null;
    }
}
