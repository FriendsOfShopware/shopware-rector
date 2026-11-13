<?php

namespace Shopware\Tests\Rector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->import('services/subscriber.xml');
    $container->import(__DIR__ . '/services/gateway.xml');
    $container->import(\dirname(__DIR__) . '/config/' . 'packages.xml', 'xml', true);
    $container->import('services/*.xml');

    if ($container->env() === 'dev') {
        $container->import('services/dev.xml');
    }
};

?>
-----
<?php

namespace Shopware\Tests\Rector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->import('services/subscriber.yaml');
    $container->import(__DIR__ . '/services/gateway.yaml');
    $container->import(\dirname(__DIR__) . '/config/' . 'packages.yaml', 'yaml', true);
    $container->import('services/*.yaml');

    if ($container->env() === 'dev') {
        $container->import('services/dev.yaml');
    }
};

?>
