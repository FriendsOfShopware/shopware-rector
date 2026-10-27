<?php

function context(\Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique $constraint): void
{
    $constraint->getContext();
    $constraint?->getContext();
}
-----
<?php

function context(\Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique $constraint): void
{
    $constraint->getSalesChannelContext()->getContext();
    $constraint?->getSalesChannelContext()->getContext();
}
