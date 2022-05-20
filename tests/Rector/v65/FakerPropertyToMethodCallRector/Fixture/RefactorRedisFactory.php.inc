<?php
namespace Frosh\Rector\Tests\Rector\v65\FakerPropertyToMethodCallRector\Fixture;

use Faker\Generator;

$a = new Generator();
$a->randomDigit;
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\FakerPropertyToMethodCallRector\Fixture;

use Faker\Generator;

$a = new Generator();
$a->randomDigit();
?>
