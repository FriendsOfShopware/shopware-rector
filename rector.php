<?php

declare(strict_types=1);

use Frosh\Rector\Set\ShopwareSetList;
use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        ShopwareSetList::SHOPWARE_6_4
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/FroshPlatformMailArchive',
        __DIR__ . '/../sw6/platform/src/Core'
    ]);
};
