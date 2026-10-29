# Shopware 6.7 forward compatibility

The 6.7 set includes migrations that prepare extensions for 6.8 using APIs already supported in **Shopware 6.7.0.0**.

```php
use Frosh\Rector\Set\ShopwareSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([ShopwareSetList::SHOPWARE_6_7_0]);
```

Run Rector with your extension's dependencies available for type analysis, review the diff, and run your extension tests.
The 6.8 set also imports these rules.
The rule classes retain their `Rule\v68` namespace to identify the upgrade they prepare for.

## Included migrations

### Native reflection

`ReflectionHelperToNativeReflectionRector` replaces the Shopware test helper's `getMethod()`, `getProperty()`, `getPropertyValue()` and `getFileName()` calls with native reflection.
The [6.7.0.0 helper](https://github.com/shopware/shopware/blob/v6.7.0.0/src/Core/Framework/Test/TestCaseHelper/ReflectionHelper.php) wraps the same native operations supported by the PHP 8.2 baseline.

```diff
-ReflectionHelper::getProperty(MyClass::class, 'name');
+new \ReflectionProperty(objectOrClass: MyClass::class, property: 'name');
```

The rule skips argument unpacking and property-value reads that would evaluate an object-producing expression twice.

### Filterable price definitions

`FilterablePriceDefinitionRector` adds `FilterableInterface` to price definitions that already declare a public, non-static, parameterless `getFilter()` returning `Rule` or `?Rule`.
The [interface already exists in 6.7.0.0](https://github.com/shopware/shopware/blob/v6.7.0.0/src/Core/Checkout/Cart/Price/Struct/FilterableInterface.php).

```diff
-class CustomPrice implements PriceDefinitionInterface
+class CustomPrice implements PriceDefinitionInterface, FilterableInterface
```

Adding the interface makes a custom price definition eligible for the interface checks already used by 6.7 promotion discount packaging.
Test the affected promotions.

## Compatibility boundaries

The baseline is 6.7.0.0, rather than the latest 6.7 patch.
Other proposed 6.8 migrations cannot be enabled wholesale in this set:

| Candidate | Reason |
| --- | --- |
| Result collection delegation | In [6.7.0.0](https://github.com/shopware/shopware/blob/v6.7.0.0/src/Core/Framework/DataAbstractionLayer/Search/EntitySearchResult.php), `filter()` and `slice()` return result wrappers with metadata, and mutations can affect separate wrapper and entity-collection storage. |
| Email constraint context | [The 6.7 constructor](https://github.com/shopware/shopware/blob/v6.7.0.0/src/Core/Checkout/Customer/Validation/Constraint/CustomerEmailUnique.php) accepts separate framework and sales-channel contexts; replacing one with the other can change the context returned. |
| Constraint constructors, exception factories, configuration getters and renamed maintenance APIs | The replacement signatures or methods are missing from 6.7.0.0. |
| Checkout permissions, moved classes, product-stream and number-range abstractions | Their replacement classes are missing from 6.7.0.0. |
| Entity-definition constructor removal | The 6.7 parent constructor still initializes the definition's class name. |
| Mixed helper/type rules | Individual replacements may exist earlier, but the complete rules also reference later APIs, such as `ArrayValues` or the abstract product-stream builder. |
| Removed cache states, profile labels, primary order associations and remote-thumbnail assignments | These depend on changed behavior or data contracts; the presence of a replacement symbol alone does not establish 6.7 compatibility. |
