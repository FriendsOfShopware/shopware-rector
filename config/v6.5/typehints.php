<?php

declare(strict_types=1);

use Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefault;
use Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefaultRector;
use Frosh\Rector\Rule\v65\AddBanAllToReverseProxyRector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeCombinator;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $nullableStringArrayType = TypeCombinator::union(new ArrayType(new StringType(), new StringType()), new NullType());

    $rectorConfig->ruleWithConfiguration(
        AddParamTypeDeclarationRector::class,
        [
            new AddParamTypeDeclaration('Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Storefront\Theme\DataAbstractionLayer\ThemeIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Core\Content\Flow\Indexing\FlowIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Core\Content\Media\DataAbstractionLayer\MediaFolderConfigurationIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Core\Content\Media\DataAbstractionLayer\MediaFolderIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Core\Content\Media\DataAbstractionLayer\MediaIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Core\Content\LandingPage\DataAbstractionLayer\LandingPageIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Core\Content\ProductStream\DataAbstractionLayer\ProductStreamIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Core\Content\Rule\DataAbstractionLayer\RuleIndexer', 'iterate', 0, $nullableStringArrayType),
            new AddParamTypeDeclaration('Shopware\Storefront\Page\Product\Review\ReviewLoaderResult', 'setMatrix', 0, new ObjectType('Shopware\Core\Content\Product\SalesChannel\Review\RatingMatrix')),
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        AddReturnTypeDeclarationRector::class,
        [
            new AddReturnTypeDeclaration('Shopware\Core\Framework\Adapter\Twig\TemplateIterator', 'getIterator', new ObjectType('Traversable')),
            new AddReturnTypeDeclaration('Shopware\Core\Content\Cms\DataResolver\CriteriaCollection', 'getIterator', new ObjectType('Traversable')),
            new AddReturnTypeDeclaration('Shopware\Core\Checkout\Cart\CartBehavior', 'hasPermission', new BooleanType()),
            new AddReturnTypeDeclaration('Shopware\Storefront\Page\Product\Review\ReviewLoaderResult', 'getMatrix', new ObjectType('Shopware\Core\Content\Product\SalesChannel\Review\RatingMatrix')),
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        AddArgumentToClassWithoutDefaultRector::class,
        [
            new AddArgumentToClassWithoutDefault('Shopware\Storefront\Framework\Captcha\AbstractCaptcha', 'supports', 1, 'captchaConfig', new ArrayType(new StringType(), new StringType())),
            new AddArgumentToClassWithoutDefault('Shopware\Storefront\Framework\Cache\ReverseProxy\AbstractReverseProxyGateway', 'tag', 2, 'response', new ObjectType('Symfony\Component\HttpFoundation\Response')),
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        ArgumentAdderRector::class,
        [
            new ArgumentAdder('Shopware\Core\Content\Media\Thumbnail\ThumbnailService', 'updateThumbnails', 2, 'strict', false, new BooleanType()),
        ],
    );

    $rectorConfig->rule(AddBanAllToReverseProxyRector::class);
};
