<?php
namespace Frosh\Rector\Tests\Rector\v65\RedisConnectionFactoryCreateRector\Fixture;

use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;

$thumbnail = new ThumbnailService();

$thumbnail->generateThumbnails($media, $context);
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\RedisConnectionFactoryCreateRector\Fixture;

use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;

$thumbnail = new ThumbnailService();

$thumbnail->generate(new MediaCollection([$media]), $context);
?>
