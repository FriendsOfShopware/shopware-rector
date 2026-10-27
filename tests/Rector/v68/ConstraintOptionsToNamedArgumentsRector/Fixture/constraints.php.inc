<?php

use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerVatIdentification;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityNotExists;

new CustomerEmailUnique(['salesChannelContext' => $context]);
new CustomerPasswordMatches(options: ['context' => $context, 'message' => 'invalid']);
new CustomerVatIdentification(['countryId' => $countryId, 'shouldCheck' => true]);
new CustomerZipCode(['caseSensitiveCheck' => false, 'countryId' => $countryId]);
new EntityExists(['entity' => 'product', 'context' => $context, 'criteria' => $criteria]);
new EntityNotExists(null, 'product', $context, 'id', $criteria);
new CustomerEmailUnique(options: null, salesChannelContext: $context);
new CustomerZipCode(options: []);
new CustomerZipCode('country-id');
-----
<?php

use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerVatIdentification;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityNotExists;

new CustomerEmailUnique(salesChannelContext: $context);
new CustomerPasswordMatches(salesChannelContext: $context, message: 'invalid');
new CustomerVatIdentification(countryId: $countryId, shouldCheck: true);
new CustomerZipCode(caseSensitiveCheck: false, countryId: $countryId);
new EntityExists(entity: 'product', context: $context, criteria: $criteria);
new EntityNotExists(entity: 'product', context: $context, primaryProperty: 'id', criteria: $criteria);
new CustomerEmailUnique(salesChannelContext: $context);
new CustomerZipCode();
new CustomerZipCode(countryId: 'country-id');
