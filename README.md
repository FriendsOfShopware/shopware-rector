# Rector for Shopware

This project extends Rector with multiple Rules for Shopware specific. 


## Install

This package is already part of [rector/rector](http://github.com/rectorphp/rector) package, so it works out of the box.

All you need to do is install the main package, and you're good to go:

```bash
composer req frosh/shopware-rector --dev
```

## Use Sets

To add a set to your config, use `Rector\Symfony\Set\SymfonySetList` class and pick one of constants:

```php
use Frosh\Rector\Set\ShopwareSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        ShopwareSetList::SHOPWARE_6_5_0,
    ]);
};
```
