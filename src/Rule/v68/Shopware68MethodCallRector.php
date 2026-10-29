<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class Shopware68MethodCallRector extends AbstractRector
{
    private const CONTEXT_EVENTS = [
        'Shopware\Core\Content\ImportExport\Event\EnrichExportCriteriaEvent',
        'Shopware\Core\Content\ImportExport\Event\ImportExportBeforeExportRecordEvent',
        'Shopware\Core\Content\ImportExport\Event\ImportExportExceptionImportExportHandlerEvent',
        'Shopware\Core\Content\Seo\Event\SeoUrlUpdateEvent',
        'Shopware\Core\Content\Media\Event\MediaFileExtensionWhitelistEvent',
        'Shopware\Core\Content\Media\Event\UnusedMediaSearchEvent',
        'Shopware\Storefront\Theme\Event\ThemeAssignedEvent',
        'Shopware\Storefront\Theme\Event\ThemeConfigChangedEvent',
        'Shopware\Storefront\Theme\Event\ThemeConfigResetEvent',
    ];

    public function __construct(private readonly ReflectionResolver $reflectionResolver) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename calls whose replacements have different declaration contracts', [new CodeSample(
            '$event->getNullableContext();',
            '$event->getContext();',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class, NullsafeMethodCall::class, StaticCall::class];
    }

    /** @param MethodCall|NullsafeMethodCall|StaticCall $node */
    public function refactor(Node $node): ?Node
    {
        $method = $this->getName($node->name);
        $classes = match ($method) {
            'getNullableContext' => self::CONTEXT_EVENTS,
            'getPrice' => ['Shopware\Core\Checkout\Document\Zugferd\ZugferdDocument'],
            'getLabel' => ['Shopware\Core\Content\ImportExport\ImportExportProfileEntity'],
            'getLink' => ['Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity'],
            default => [],
        };
        foreach ($classes as $class) {
            $receiver = $node instanceof StaticCall ? $node->class : $node->var;
            if ($this->isObjectType($receiver, new ObjectType($class))) {
                $declaringClass = $this->reflectionResolver->resolveClassReflectionSourceObject($node);
                if ($declaringClass !== null && $declaringClass->getName() !== $class) {
                    // A custom override needs its implementation migrated too; do not bypass it.
                    return null;
                }
                if ($method === 'getLink') {
                    if ($node->args !== []) {
                        return null;
                    }
                    $node->args = [new Node\Arg(new Node\Scalar\String_('link'))];
                }
                $node->name = new Identifier(match ($method) {
                    'getPrice' => 'getPriceWithFallback',
                    'getLabel' => 'getTechnicalName',
                    'getLink' => 'getTranslation',
                    default => 'getContext',
                });

                return $node;
            }
        }

        return null;
    }
}
