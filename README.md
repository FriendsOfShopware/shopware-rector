# Rector for Shopware

This project extends Rector with multiple Rules for Shopware specific. 

See available [Shopware rules](/docs/rector_rules_overview.md)

For migrations already supported on Shopware 6.7.0.0, see the [6.7 forward compatibility guide](docs/shopware-6.7.md).


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

The `SHOPWARE_6_8_0` set includes generic BC-change rules configured for Shopware 6.8.
They add announced optional parameters to overrides, widen parameter types, narrow return types, and make current defaults explicit at call sites.
Changes are configured directly in `config/v6.8/bc-changes.php`; there is no minimum/target-version configuration or generated migration history.

## Regenerate BC changes

Run the generator against a Shopware source checkout with its Composer dependencies installed:

```bash
php bin/generate-bc-change-config.php /path/to/shopware/vendor/autoload.php v6.8.0 config/v6.8/bc-changes.php
```

It scans Shopware's `src/` tree and reads BC attributes through reflection, without requiring an optimized Composer class map.
The version selects the announced changes to generate for the set; it does not introduce runtime version-range configuration.
The generated config also maps `ClassMoved` attributes through Rector's existing class-renaming rule.
Other BC attributes that require implementation decisions remain manual migrations.

## Use directly the config

```bash
# Clone this repo

composer install

# Dry Run
./vendor/bin/rector process --config config/shopware-6.7.0.php --autoload-file [SHOPWARE]/vendor/autoload.php [SHOPWARE]/custom/plugins/MyPlugin --dry-run

# Normal Run
./vendor/bin/rector process --config config/shopware-6.7.0.php --autoload-file [SHOPWARE]/vendor/autoload.php [SHOPWARE]/custom/plugins/MyPlugin
```
