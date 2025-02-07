<?php
namespace Frosh\Rector\Tests\Rector\v65\RedisConnectionFactoryCreateRector\Fixture;

use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;

$thumbnail = new ThumbnailService();

$thumbnail->generateThumbnails($media, $context);
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\RedisConnectionFactoryCreateRector\Fixture;

use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;

$thumbnail = new ThumbnailService();

$thumbnail->generate(new \Shopware\Core\Content\Media\MediaCollection([$media]), $context);
?>
