<?php

declare(strict_types=1);

use Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefault;
use Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefaultRector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $rectorConfig->ruleWithConfiguration(
        AddArgumentToClassWithoutDefaultRector::class,
        [
            new AddArgumentToClassWithoutDefault('AbstractCaptcha', 'supports', 1, 'captcha', new ArrayType(new StringType(), new StringType())),
        ]
    );
};
