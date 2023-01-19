<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $ecsConfig->parallel();

    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);

    $ecsConfig->import(SetList::PSR_12);
    $ecsConfig->import(SetList::ARRAY);
    $ecsConfig->import(SetList::CLEAN_CODE);
    $ecsConfig->import(SetList::STRICT);
};
