<?php

use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerVatIdentification;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityNotExists;

new CustomerEmailUnique($options);
new CustomerEmailUnique([...$options]);
new CustomerEmailUnique(['groups' => ['custom']]);
new CustomerEmailUnique(['salesChannelContext' => $context, 'context' => $frameworkContext]);
new CustomerEmailUnique(['salesChannelContext' => $context], $otherContext);
new CustomerPasswordMatches(['context' => $context, 'salesChannelContext' => $otherContext]);
new CustomerEmailUnique(salesChannelContext: $context);
new CustomerEmailUnique(...$args);
new \UnrelatedConstraint(['salesChannelContext' => $context]);
