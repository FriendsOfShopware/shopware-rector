# Rector for Shopware

This project extends Rector with multiple Rules for Shopware specific. 

See available [Shopware rules](/docs/rector_rules_overview.md)


## Install

Make sure to install both `frosh/shopware-rector` as well as `rector/rector`.

```bash
composer req frosh/shopware-rector --dev
```

## Use Sets

To add a set to your config, use `Frosh\Rector\Set\ShopwareSetList` class and pick one of constants:

```php
use Frosh\Rector\Set\ShopwareSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        ShopwareSetList::SHOPWARE_6_7_0,
    ]);
};
```

## Use directly the config

```bash
# Clone this repo

composer install

# Dry Run
./vendor/bin/rector process --config config/shopware-6.7.0.php --autoload-file [SHOPWARE]/vendor/autoload.php [SHOPWARE]/custom/plugins/MyPlugin --dry-run

# Normal Run
./vendor/bin/rector process --config config/shopware-6.7.0.php --autoload-file [SHOPWARE]/vendor/autoload.php [SHOPWARE]/custom/plugins/MyPlugin
```
