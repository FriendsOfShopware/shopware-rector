# 11 Rules Overview

## AddArgumentToClassWithoutDefaultRector

This Rector adds new default arguments in calls of defined methods and class types.

:wrench: **configure it!**

- class: [`Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefaultRector`](../src/Rule/ClassMethod/AddArgumentToClassWithoutDefaultRector.php)

```php
<?php

declare(strict_types=1);

use Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefault;
use Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefaultRector;
use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddArgumentToClassWithoutDefaultRector::class, [
        new AddArgumentToClassWithoutDefault('SomeExampleClass', 'someMethod', 0, new ObjectType('SomeType'), 'someArgument'),
    ]);
};
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->someMethod();
+$someObject->someMethod(true);

 class MyCustomClass extends SomeExampleClass
 {
-    public function someMethod()
+    public function someMethod($value)
     {
     }
 }
```

<br>

## AddBanAllToReverseProxyRector

Adds banAll method to reverse proxy

- class: [`Frosh\Rector\Rule\v65\AddBanAllToReverseProxyRector`](../src/Rule/v65/AddBanAllToReverseProxyRector.php)

```diff
 class Test extends \Shopware\Core\Framework\Cache\ReverseProxy\AbstractReverseProxyGateway {
-
+    public function banAll(): void
+    {
+    }
 }
```

<br>

## ContextMetadataExtensionToStateRector

Migrate extension metadata to state rector

- class: [`Frosh\Rector\Rule\v65\ContextMetadataExtensionToStateRector`](../src/Rule/v65/ContextMetadataExtensionToStateRector.php)

```diff
-$context->addExtension(EntityIndexerRegistry::USE_INDEXING_QUEUE, new ArrayEntity());
+$context->addState(EntityIndexerRegistry::USE_INDEXING_QUEUE);
```

<br>

## FakerPropertyToMethodCallRector

Move faker property to method call

- class: [`Frosh\Rector\Rule\v65\FakerPropertyToMethodCallRector`](../src/Rule/v65/FakerPropertyToMethodCallRector.php)

```diff
-$this->faker->randomDigit
+$this->faker->randomDigit()
```

<br>

## InterfaceReplacedWithAbstractClassRector

Replace UrlProviderInterface with AbstractClass

:wrench: **configure it!**

- class: [`Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClassRector`](../src/Rule/Class_/InterfaceReplacedWithAbstractClassRector.php)

```php
<?php

declare(strict_types=1);

use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClass;
use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClassRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(InterfaceReplacedWithAbstractClassRector::class, [
        new InterfaceReplacedWithAbstractClass('Foo', 'AbstractTest'),
    ]);
};
```

↓

```diff
-class Foo implements Test {
+class Foo extends AbstractTest {

 }
```

<br>

## MakeClassConstructorArgumentRequiredRector

NAME

:wrench: **configure it!**

- class: [`Frosh\Rector\Rule\ClassConstructor\MakeClassConstructorArgumentRequiredRector`](../src/Rule/ClassConstructor/MakeClassConstructorArgumentRequiredRector.php)

```php
<?php

declare(strict_types=1);

use Frosh\Rector\Rule\ClassConstructor\MakeClassConstructorArgumentRequired;
use Frosh\Rector\Rule\ClassConstructor\MakeClassConstructorArgumentRequiredRector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(MakeClassConstructorArgumentRequiredRector::class, [
        new MakeClassConstructorArgumentRequired('Foo', 0, new ArrayType(new StringType(), new StringType())),
    ]);
};
```

↓

```diff
 class Foo {
-    public function __construct(array $foo = [])
+    public function __construct(array $foo)
     {
     }
 }
```

<br>

## MigrateCaptchaAnnotationToRouteRector

NAME

- class: [`Frosh\Rector\Rule\v65\MigrateCaptchaAnnotationToRouteRector`](../src/Rule/v65/MigrateCaptchaAnnotationToRouteRector.php)

```diff
 class Foo
 {
     /**
-     * @Route("/form/contact", name="frontend.form.contact.send", methods={"POST"}, defaults={"XmlHttpRequest"=true})
-     * @Captcha
+     * @Route("/form/contact", name="frontend.form.contact.send", methods={"POST"}, defaults={"XmlHttpRequest"=true, "_captcha"=true})
      */
-    public function sendContactForm()
+    public function sendContactForm(): Response
     {
     }
 }
```

<br>

## MigrateLoginRequiredAnnotationToRouteRector

Migrates Annotations to Route annotation

- class: [`Frosh\Rector\Rule\v65\MigrateLoginRequiredAnnotationToRouteRector`](../src/Rule/v65/MigrateLoginRequiredAnnotationToRouteRector.php)

```diff
-@LoginRequired
-@Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"})
+@Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"}, defaults={"_loginRequired"=true})
 public function myAction()
```

<br>

## MigrateRouteScopeToRouteDefaults

NAME

- class: [`Frosh\Rector\Rule\v65\MigrateRouteScopeToRouteDefaults`](../src/Rule/v65/MigrateRouteScopeToRouteDefaults.php)

```diff
 /**
- * @RouteScope(scopes={"storefront"})
+ * @Route(defaults={"_routeScope"={"storefront"}})
  */
 class Controller
 {
 }
```

<br>

## RemoveArgumentFromClassConstructRector

This Rector removes an argument in the defined class construct.

:wrench: **configure it!**

- class: [`Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstructRector`](../src/Rule/ClassConstructor/RemoveArgumentFromClassConstructRector.php)

```php
<?php

declare(strict_types=1);

use Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstruct;
use Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstructRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RemoveArgumentFromClassConstructRector::class, [
        new RemoveArgumentFromClassConstruct('SomeExampleClass', 0),
    ]);
};
```

↓

```diff
-$someObject = new SomeExampleClass($example);
+$someObject = new SomeExampleClass();
```

<br>

## ThumbnailGenerateSingleToMultiGenerateRector

Move single thumbnail generation call to batch

- class: [`Frosh\Rector\Rule\v65\ThumbnailGenerateSingleToMultiGenerateRector`](../src/Rule/v65/ThumbnailGenerateSingleToMultiGenerateRector.php)

```diff
-$thumbnail->generateThumbnails($media, $context);
+$thumbnail->generate(new MediaCollection([$media]), $context);
```

<br>
