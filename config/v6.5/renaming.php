<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v65\MigrateLoginRequiredAnnotationToRouteRector;
use Frosh\Rector\Rule\v65\RedisConnectionFactoryCreateRector;
use Frosh\Rector\Rule\v65\ThemeCompilerPrefixRector;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ObjectType;
use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(
        RenameMethodRector::class,
        [
            new MethodCallRename('Shopware\\Core\\Framework\\Adapter\\Twig\\EntityTemplateLoader', 'clearInternalCache', 'reset'),
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        RenameClassRector::class,
        [
            'Shopware\\Core\\Framework\\Adapter\\Asset\\ThemeAssetPackage' => 'Shopware\\Storefront\\Theme\\ThemeAssetPackage',
            'Maltyxx\\ImagesGenerator\\ImagesGeneratorProvider' => 'bheller\\ImagesGenerator\\ImagesGeneratorProvider',
            'Shopware\\Core\\Framework\\Event\\BusinessEventInterface' => 'Shopware\\Core\\Framework\\Event\\FlowEventAware',
            'Shopware\\Core\\Framework\\Event\\MailActionInterface' => 'Shopware\\Core\\Framework\\Event\\MailAware',
            'Shopware\\Core\\Framework\\Log\\LogAwareBusinessEventInterface' => 'Shopware\\Core\\Framework\\Log\\LogAware',
            'Shopware\\Storefront\\Event\\ProductExportContentTypeEvent' => 'Shopware\\Core\\Content\\ProductExport\\Event\\ProductExportContentTypeEvent',
            'Shopware\\Storefront\\Page\\Product\\Review\\MatrixElement' => 'Shopware\\Core\\Content\\Product\\SalesChannel\\Review\\MatrixElement',
            'Shopware\\Storefront\\Page\\Product\\Review\\RatingMatrix' => 'Shopware\\Core\\Content\\Product\\SalesChannel\\Review\\RatingMatrix',
            'Shopware\\Storefront\\Page\\Address\\Listing\\AddressListingCriteriaEvent' => 'Shopware\\Core\\Checkout\\Customer\\Event\\AddressListingCriteriaEvent',
            'Shopware\\Administration\\Service\\AdminOrderCartService' => 'Shopware\\Core\\Checkout\\Cart\\ApiOrderCartService',
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        RemoveMethodCallParamRector::class,
        [
            new RemoveMethodCallParam('Shopware\\Core\\Checkout\\Cart\\Tax\\Struct\\CalculatedTaxCollection', 'merge', 1),
        ],
    );

    $rectorConfig->rule(MigrateLoginRequiredAnnotationToRouteRector::class);
    $rectorConfig->rule(RedisConnectionFactoryCreateRector::class);
    $rectorConfig->rule(ThemeCompilerPrefixRector::class);
};
