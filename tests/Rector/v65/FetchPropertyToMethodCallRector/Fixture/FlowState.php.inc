<?php
namespace Frosh\Rector\Tests\Rector\v65\FetchPropertyToMethodCallRector\Fixture;

use Shopware\Core\Content\Flow\Dispatching\FlowState;

$a = new FlowState();
$a->sequenceId;
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\FetchPropertyToMethodCallRector\Fixture;

use Shopware\Core\Content\Flow\Dispatching\FlowState;

$a = new FlowState();
$a->getSequenceId();
?>
