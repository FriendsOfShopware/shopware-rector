<?php

use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;

ReflectionHelper::getMethod(Foo::class, 'run');
ReflectionHelper::getProperty(className: Foo::class, propertyName: 'name');
ReflectionHelper::getFileName(Foo::class);
ReflectionHelper::getPropertyValue($object, 'name');
-----
<?php

use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;

new \ReflectionMethod(objectOrClass: Foo::class, method: 'run');
new \ReflectionProperty(objectOrClass: Foo::class, property: 'name');
(new \ReflectionClass(objectOrClass: Foo::class))->getFileName();
(new \ReflectionProperty(objectOrClass: $object, property: 'name'))->getValue($object);
