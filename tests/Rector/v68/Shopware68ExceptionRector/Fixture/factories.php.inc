<?php

new \Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidPriceFieldTypeException(type: 'test');
new \Shopware\Core\System\SystemConfig\Exception\InvalidDomainException(domain: 'test');
new \Shopware\Core\System\SystemConfig\Exception\InvalidKeyException(key: 'test');
new \Shopware\Core\System\SystemConfig\Exception\InvalidSettingValueException(key: 'test', neededType: 'test', actualType: 'test');
new \Shopware\Core\System\NumberRange\Exception\IncrementStorageNotFoundException(configuredStorage: 'test', availableStorages: []);
new \Shopware\Core\System\NumberRange\Exception\NoConfigurationException(entityName: 'test', salesChannelId: 'test');
new \Shopware\Storefront\Theme\Exception\ThemeAssignmentException(themeName: 'test', themeSalesChannel: [], childThemeSalesChannel: [], assignedSalesChannels: [], e: null);
new \Shopware\Storefront\Theme\Exception\InvalidThemeBundleException(themeName: 'test');
new \Shopware\Storefront\Framework\Media\Exception\MediaValidatorMissingException(type: 'test');
-----
<?php

\Shopware\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException::invalidPriceFieldType(type: 'test');
\Shopware\Core\System\SystemConfig\SystemConfigException::invalidDomain(domain: 'test');
\Shopware\Core\System\SystemConfig\SystemConfigException::invalidKey(key: 'test');
\Shopware\Core\System\SystemConfig\SystemConfigException::invalidSettingValueException(key: 'test', expectedType: 'test', actualType: 'test');
\Shopware\Core\System\NumberRange\NumberRangeException::incrementStorageNotFound(storage: 'test', availableStorages: []);
\Shopware\Core\System\NumberRange\NumberRangeException::noConfigurationForEntity(entity: 'test', salesChannelId: 'test');
\Shopware\Storefront\Theme\Exception\ThemeException::themeAssignmentException(themeName: 'test', themeSalesChannel: [], childThemeSalesChannel: [], assignedSalesChannels: [], e: null);
\Shopware\Storefront\Theme\Exception\ThemeException::invalidThemeBundle(themeName: 'test');
\Shopware\Storefront\Framework\StorefrontFrameworkException::mediaValidatorMissing(type: 'test');
