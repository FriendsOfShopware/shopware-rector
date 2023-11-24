<?php

declare(strict_types=1);

use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClass;
use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClassRector;
use Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstruct;
use Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstructRector;
use Frosh\Rector\Rule\v65\FakerPropertyToMethodCallRector;
use Frosh\Rector\Rule\v65\MigrateCaptchaAnnotationToRouteRector;
use Frosh\Rector\Rule\v65\MigrateLoginRequiredAnnotationToRouteRector;
use Frosh\Rector\Rule\v65\MigrateRouteScopeToRouteDefaults;
use Frosh\Rector\Rule\v65\ThumbnailGenerateSingleToMultiGenerateRector;
use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(
        RenameMethodRector::class,
        [
            new MethodCallRename('Shopware\\Core\\Framework\\Adapter\\Twig\\EntityTemplateLoader', 'clearInternalCache', 'reset'),
            new MethodCallRename('Shopware\Core\Content\ImportExport\Processing\Mapping\Mapping', 'getDefault', 'getDefaultValue'),
            new MethodCallRename('Shopware\Core\Content\ImportExport\Processing\Mapping\Mapping', 'getMappedDefault', 'getDefaultValue'),
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
            'Shopware\\Core\\System\\User\\Service\\UserProvisioner' => 'Shopware\\Core\\Maintenance\\User\\Service\\UserProvisioner',
            'Shopware\\Core\\Framework\\DataAbstractionLayer\\EntityRepositoryInterface' => 'Shopware\\Core\\Framework\\DataAbstractionLayer\\EntityRepository',
            'Shopware\\Core\\System\\SalesChannel\\Entity\\SalesChannelRepositoryInterface' => 'Shopware\\Core\\System\\SalesChannel\\Entity\\SalesChannelRepository',
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        RemoveMethodCallParamRector::class,
        [
            new RemoveMethodCallParam('Shopware\\Core\\Checkout\\Cart\\Tax\\Struct\\CalculatedTaxCollection', 'merge', 1),
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        RemoveArgumentFromClassConstructRector::class,
        [
            new RemoveArgumentFromClassConstruct('Shopware\\Core\\Checkout\\Customer\\Exception\\DuplicateWishlistProductException', 0),
            new RemoveArgumentFromClassConstruct('Shopware\Core\Content\Newsletter\Exception\LanguageOfNewsletterDeleteException', 0),
            new RemoveArgumentFromClassConstruct('Shopware\Core\Content\Product\Events\ProductIndexerEvent', 1),
            new RemoveArgumentFromClassConstruct('Shopware\Core\Content\Product\Events\ProductIndexerEvent', 2),
        ],
    );

    $rectorConfig->rule(MigrateLoginRequiredAnnotationToRouteRector::class);
    $rectorConfig->rule(MigrateCaptchaAnnotationToRouteRector::class);
    $rectorConfig->rule(MigrateRouteScopeToRouteDefaults::class);
    $rectorConfig->rule(ThumbnailGenerateSingleToMultiGenerateRector::class);

    $rectorConfig->ruleWithConfiguration(
        PropertyFetchToMethodCallRector::class,
        [new PropertyFetchToMethodCall(
            'Shopware\Core\Content\Flow\Dispatching\FlowState',
            'sequenceId',
            'getSequenceId',
            null,
        )],
    );

    $rectorConfig->ruleWithConfiguration(
        InterfaceReplacedWithAbstractClassRector::class,
        [
            new InterfaceReplacedWithAbstractClass('Shopware\Core\Checkout\Cart\CartPersisterInterface', 'Shopware\Core\Checkout\Cart\AbstractCartPersister'),
            new InterfaceReplacedWithAbstractClass('Shopware\Core\Content\Sitemap\Provider\UrlProviderInterface', 'Shopware\Core\Content\Sitemap\Provider\AbstractUrlProvider'),
            new InterfaceReplacedWithAbstractClass('Shopware\Core\System\Snippet\Files\SnippetFileInterface', 'Shopware\Core\System\Snippet\Files\GenericSnippetFile'),
        ],
    );

    $rectorConfig->rule(FakerPropertyToMethodCallRector::class);
};
