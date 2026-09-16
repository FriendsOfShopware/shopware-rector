<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Rule\BCChange\ValueObject\AddOptionalParameter;
use Frosh\Rector\Rule\BCChange\ValueObject\AddRequiredParameter;
use Frosh\Rector\Rule\BCChange\ValueObject\BCChange;
use Frosh\Rector\Rule\BCChange\ValueObject\BCChangeRuleConfiguration;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterDefault;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterName;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterType;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeReturnType;
use Frosh\Rector\Rule\BCChange\ValueObject\RemoveParameter;
use Frosh\Rector\Version\ShopwareVersionRange;
use Rector\Configuration\RectorConfigBuilder;

final readonly class BCChangeConfiguration
{
    /** @param list<BCChange> $changes */
    public function __construct(public array $changes) {}

    public function register(RectorConfigBuilder $rectorConfig, ShopwareVersionRange $versions): void
    {
        $rules = [
            AddOptionalParameter::class => AddOptionalParameterRector::class,
            AddRequiredParameter::class => AddRequiredParameterRector::class,
            ChangeParameterDefault::class => ParameterDefaultValueChangeRector::class,
            ChangeParameterName::class => ParameterNameChangeRector::class,
            ChangeParameterType::class => ParameterTypeWideningRector::class,
            ChangeReturnType::class => ReturnTypeNarrowingRector::class,
            RemoveParameter::class => ParameterRemovalRector::class,
        ];

        foreach ($rules as $configurationClass => $rectorClass) {
            $changes = array_values(array_filter($this->changes, static fn (BCChange $change): bool => $change instanceof $configurationClass));
            if ($changes !== []) {
                $rectorConfig->withConfiguredRule($rectorClass, [new BCChangeRuleConfiguration($versions, $changes)]);
            }
        }
    }
}
