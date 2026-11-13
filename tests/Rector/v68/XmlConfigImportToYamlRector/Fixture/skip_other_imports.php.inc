<?php

namespace Shopware\Tests\Rector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container, object $loader, string $file): void {
    $container->import('services.yaml');
    $container->import('services.php');
    $container->import($file);
    $loader->import('feed.xml');
};
