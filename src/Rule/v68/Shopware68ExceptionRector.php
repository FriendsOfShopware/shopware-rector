<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class Shopware68ExceptionRector extends AbstractRector
{
    private const FACTORIES = [
        'Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidPriceFieldTypeException' => ['Shopware\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException', 'invalidPriceFieldType', ['type'], ['type']],
        'Shopware\Core\System\SystemConfig\Exception\InvalidDomainException' => ['Shopware\Core\System\SystemConfig\SystemConfigException', 'invalidDomain', ['domain'], ['domain']],
        'Shopware\Core\System\SystemConfig\Exception\InvalidKeyException' => ['Shopware\Core\System\SystemConfig\SystemConfigException', 'invalidKey', ['key'], ['key']],
        'Shopware\Core\System\SystemConfig\Exception\InvalidSettingValueException' => ['Shopware\Core\System\SystemConfig\SystemConfigException', 'invalidSettingValueException', ['key', 'neededType', 'actualType'], ['key', 'expectedType', 'actualType']],
        'Shopware\Core\System\NumberRange\Exception\IncrementStorageNotFoundException' => ['Shopware\Core\System\NumberRange\NumberRangeException', 'incrementStorageNotFound', ['configuredStorage', 'availableStorages'], ['storage', 'availableStorages']],
        'Shopware\Core\System\NumberRange\Exception\NoConfigurationException' => ['Shopware\Core\System\NumberRange\NumberRangeException', 'noConfigurationForEntity', ['entityName', 'salesChannelId'], ['entity', 'salesChannelId']],
        'Shopware\Storefront\Theme\Exception\ThemeAssignmentException' => ['Shopware\Storefront\Theme\Exception\ThemeException', 'themeAssignmentException', ['themeName', 'themeSalesChannel', 'childThemeSalesChannel', 'assignedSalesChannels', 'e'], ['themeName', 'themeSalesChannel', 'childThemeSalesChannel', 'assignedSalesChannels', 'e']],
        'Shopware\Storefront\Theme\Exception\InvalidThemeBundleException' => ['Shopware\Storefront\Theme\Exception\ThemeException', 'invalidThemeBundle', ['themeName'], ['themeName']],
        'Shopware\Storefront\Framework\Media\Exception\MediaValidatorMissingException' => ['Shopware\Storefront\Framework\StorefrontFrameworkException', 'mediaValidatorMissing', ['type'], ['type']],
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Construct removed Shopware exceptions through domain factories', [new CodeSample(
            'throw new InvalidKeyException($key);',
            'throw SystemConfigException::invalidKey(key: $key);',
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

        $factory = self::FACTORIES[$this->getName($node->class)] ?? null;
        if ($factory === null) {
            return null;
        }

        [$class, $method, $before, $after] = $factory;
        $args = NamedArguments::resolve($node->args, $before);
        if ($args === null) {
            return null;
        }

        if ($method === 'invalidSettingValueException') {
            // The replacement requires all three strings; missing/null type names need a decision.
            if (count($args) !== 3 || !$this->getType($args['neededType']->value)->isString()->yes() || !$this->getType($args['actualType']->value)->isString()->yes()) {
                return null;
            }
        }

        foreach ($before as $index => $name) {
            if (isset($args[$name])) {
                $args[$name]->name = new Identifier($after[$index] ?? $name);
            }
        }

        if ($method === 'incrementStorageNotFound' && !isset($args['availableStorages'])) {
            $args['availableStorages'] = new Node\Arg(new Node\Expr\Array_(), name: new Identifier('availableStorages'));
        }

        return new StaticCall(new FullyQualified($class), $method, array_values($args));
    }
}
