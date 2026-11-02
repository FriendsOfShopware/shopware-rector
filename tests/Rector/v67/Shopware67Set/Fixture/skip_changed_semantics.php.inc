<?php

namespace ChangedSemantics;

use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

function inspect(EntitySearchResult $result, CustomerEmailUnique $constraint): void
{
    $filtered = $result->filter(static fn ($entity) => true);
    $filtered->getTotal();
    $result->clear();
    foreach ($result as $entity) {
    }
    $constraint->getContext();
}
