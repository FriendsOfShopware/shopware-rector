<?php
namespace Frosh\Rector\Tests\Rector\v65\RedisConnectionFactoryCreateRector\Fixture;

use Shopware\Storefront\Theme\ThemeCompiler;

ThemeCompiler::getThemePrefix('bla');
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\RedisConnectionFactoryCreateRector\Fixture;

use Shopware\Storefront\Theme\MD5ThemePathBuilder;
use Shopware\Storefront\Theme\ThemeCompiler;
$themePrefixer = new MD5ThemePathBuilder();

$themePrefixer->assemblePath('bla');
?>
