<?php

namespace Shopware\Tests\Rector\Fixture;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('routes/admin.xml');
    $routes->import('../Controller', 'attribute');
};

?>
-----
<?php

namespace Shopware\Tests\Rector\Fixture;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('routes/admin.yaml');
    $routes->import('../Controller', 'attribute');
};

?>
