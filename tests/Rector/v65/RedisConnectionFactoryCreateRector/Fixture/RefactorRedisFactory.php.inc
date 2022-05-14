<?php
namespace Frosh\Rector\Tests\Rector\v65\RedisConnectionFactoryCreateRector\Fixture;

use Shopware\Core\Framework\Adapter\Cache\RedisConnectionFactory;

RedisConnectionFactory::createConnection('redis://localhost');
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\RedisConnectionFactoryCreateRector\Fixture;

use Shopware\Core\Framework\Adapter\Cache\RedisConnectionFactory;
$redisFactory = new RedisConnectionFactory();

$redisFactory->create('redis://localhost');
?>
