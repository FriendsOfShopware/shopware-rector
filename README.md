# Rector for Shopware

This project extends Rector with multiple Rules for Shopware specific. 

See available [Shopware rules](/docs/rector_rules_overview.md)


## Install

Make sure to install both `frosh/shopware-rector` as well as `rector/rector`.

```bash
composer req frosh/shopware-rector --dev
```

## Use Sets

Configure the minimum Shopware version supported by the project and the version it is preparing for:

```php
use Rector\Config\RectorConfig;
use Frosh\Rector\Set\ShopwareSet;

return ShopwareSet::forVersionRange(
    RectorConfig::configure(),
    minimumVersion: '6.7.0',
    targetVersion: '6.8.0',
);
```

Handwritten migrations use the same range: target-only rules run once their effective version is
included in the minimum, while verified bridge rules may run earlier when their replacement API is
available in every supported version. The existing `ShopwareSetList::SHOPWARE_6_X` constants remain
available for their original one-version behavior.

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

Shopware's BC-change attributes are stored in a generated, historical manifest and are included by
the version-range set above. They can also be configured directly:

```php
use Frosh\Rector\Rule\BCChange\BCChangeRector;
use Frosh\Rector\Set\BCChangeSet;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(
        BCChangeRector::class,
        BCChangeSet::forVersionRange(
            minimumVersion: '6.7.0',
            targetVersion: '6.8.0',
        ),
    );
```

Changes newer than the target are ignored. Changes newer than the minimum use a transformation
that remains compatible with both Shopware versions. Changes included in the minimum use the
target-only migration, allowing obsolete compatibility code to be removed.

Regenerate one version from an optimized Shopware Composer class map:

```bash
composer dump-autoload --optimize
./bin/generate-bc-change-config.php [SHOPWARE]/vendor/autoload.php v6.8.0 config/bc-changes.php
```

The generator replaces only entries for the requested version, preserving older changes for later
target-only migrations. It covers `NewOptionalParameter`, `NewRequiredParameter`,
`ParameterDefaultValueChange`, `ParameterNameChange`, `ParameterRemoval`,
`ParameterTypeWidening`, and `ReturnTypeNarrowing`. Other attributes remain diagnostics until their
migration can be expressed without guessing application behavior.
