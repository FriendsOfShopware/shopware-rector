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
use Rector\Config\RectorConfig;
use Frosh\Rector\Set\ShopwareSetList;

return RectorConfig::configure()
    ->withSets([
        ShopwareSetList::SHOPWARE_6_7_0,
    ]);
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

## Generate BC-change migrations

The Shopware 6.8 set contains forward-compatible declaration and call-site changes generated from
Shopware's BC-change attributes. Regenerate them from an optimized Shopware Composer class map:

```bash
composer dump-autoload --optimize
./bin/generate-bc-change-config.php [SHOPWARE]/vendor/autoload.php v6.8.0 config/v6.8/bc-changes.php
```

The generator currently covers `NewOptionalParameter`, `ParameterDefaultValueChange`,
`ParameterTypeWidening`, and `ReturnTypeNarrowing`. Other attributes remain diagnostics until their
migration can be expressed without guessing application behavior.
