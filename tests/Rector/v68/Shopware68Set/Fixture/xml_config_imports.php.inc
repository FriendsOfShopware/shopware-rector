<?php

namespace Shopware\Tests\Rector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->import('services/subscriber.xml');
};

?>
-----
<?php

namespace Shopware\Tests\Rector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->import('services/subscriber.yaml');
};

?>
