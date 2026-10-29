<?php

use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;

ReflectionHelper::getPropertyValue(createObject(), 'name');
ReflectionHelper::getMethod(...$args);
ReflectionHelper::getMethod(...);
UnrelatedHelper::getProperty(Foo::class, 'name');
