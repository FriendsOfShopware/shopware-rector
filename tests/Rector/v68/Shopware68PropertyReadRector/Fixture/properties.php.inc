<?php

function properties(\Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique $constraint): void
{
    echo $constraint->message;
    echo $constraint?->message;
    $constraint->message = 'override';
    $constraint->message .= '!';
    unset($constraint->message);
    isset($constraint->message);
}
-----
<?php

function properties(\Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique $constraint): void
{
    echo $constraint->getMessage();
    echo $constraint?->getMessage();
    $constraint->message = 'override';
    $constraint->message .= '!';
    unset($constraint->message);
    isset($constraint->message);
}
