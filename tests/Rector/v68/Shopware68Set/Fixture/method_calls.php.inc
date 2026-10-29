<?php

function context0(\Shopware\Core\Content\ImportExport\Event\EnrichExportCriteriaEvent $event): void { $event?->getNullableContext(); }
function context1(\Shopware\Core\Content\ImportExport\Event\ImportExportBeforeExportRecordEvent $event): void { $event?->getNullableContext(); }
function context2(\Shopware\Core\Content\ImportExport\Event\ImportExportExceptionImportExportHandlerEvent $event): void { $event?->getNullableContext(); }
function context3(\Shopware\Core\Content\Seo\Event\SeoUrlUpdateEvent $event): void { $event?->getNullableContext(); }
function context4(\Shopware\Core\Content\Media\Event\MediaFileExtensionWhitelistEvent $event): void { $event?->getNullableContext(); }
function context5(\Shopware\Core\Content\Media\Event\UnusedMediaSearchEvent $event): void { $event?->getNullableContext(); }
function context6(\Shopware\Storefront\Theme\Event\ThemeAssignedEvent $event): void { $event?->getNullableContext(); }
function context7(\Shopware\Storefront\Theme\Event\ThemeConfigChangedEvent $event): void { $event?->getNullableContext(); }
function context8(\Shopware\Storefront\Theme\Event\ThemeConfigResetEvent $event): void { $event?->getNullableContext(); }
function price(\Shopware\Core\Checkout\Document\Zugferd\ZugferdDocument $document, $tax): void { $document->getPrice($tax); }
function display(\Shopware\Core\Content\ImportExport\ImportExportProfileEntity $profile, \Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity $manufacturer): void
{
    $profile->getLabel();
    $label = $profile->label;
    $manufacturer?->getLink();
}
-----
<?php

function context0(\Shopware\Core\Content\ImportExport\Event\EnrichExportCriteriaEvent $event): void { $event?->getContext(); }
function context1(\Shopware\Core\Content\ImportExport\Event\ImportExportBeforeExportRecordEvent $event): void { $event?->getContext(); }
function context2(\Shopware\Core\Content\ImportExport\Event\ImportExportExceptionImportExportHandlerEvent $event): void { $event?->getContext(); }
function context3(\Shopware\Core\Content\Seo\Event\SeoUrlUpdateEvent $event): void { $event?->getContext(); }
function context4(\Shopware\Core\Content\Media\Event\MediaFileExtensionWhitelistEvent $event): void { $event?->getContext(); }
function context5(\Shopware\Core\Content\Media\Event\UnusedMediaSearchEvent $event): void { $event?->getContext(); }
function context6(\Shopware\Storefront\Theme\Event\ThemeAssignedEvent $event): void { $event?->getContext(); }
function context7(\Shopware\Storefront\Theme\Event\ThemeConfigChangedEvent $event): void { $event?->getContext(); }
function context8(\Shopware\Storefront\Theme\Event\ThemeConfigResetEvent $event): void { $event?->getContext(); }
function price(\Shopware\Core\Checkout\Document\Zugferd\ZugferdDocument $document, $tax): void { $document->getPriceWithFallback($tax); }
function display(\Shopware\Core\Content\ImportExport\ImportExportProfileEntity $profile, \Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity $manufacturer): void
{
    $profile->getTechnicalName();
    $label = $profile->getTechnicalName();
    $manufacturer?->getTranslation('link');
}
