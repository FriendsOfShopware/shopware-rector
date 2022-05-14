<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v65\MigrateLoginRequiredAnnotationToRouteRector;
use Frosh\Rector\Rule\v65\RedisConnectionFactoryCreateRector;
use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
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
            'Shopware\\Administration\\Service\\AdminOrderCartService' => 'Shopware\\Core\\Checkout\\Cart\\ApiOrderCartService',
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        AddReturnTypeDeclarationRector::class,
        [
            new AddReturnTypeDeclaration('Shopware\\Core\\Framework\\Adapter\\Twig\\TemplateIterator', 'getIterator', new ObjectType('Traversable'))
        ]
    );


    $rectorConfig->rule(MigrateLoginRequiredAnnotationToRouteRector::class);
    $rectorConfig->rule(RedisConnectionFactoryCreateRector::class);
};
