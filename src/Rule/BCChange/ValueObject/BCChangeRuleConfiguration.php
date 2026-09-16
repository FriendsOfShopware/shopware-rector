<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange\ValueObject;

use Frosh\Rector\Version\ShopwareVersionRange;

final readonly class BCChangeRuleConfiguration
{
    /** @param list<BCChange> $changes */
    public function __construct(public ShopwareVersionRange $versions, public array $changes) {}
}
