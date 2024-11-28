<?php
namespace Frosh\Rector\Tests\Rector\v65\ContextMetadataExtensionToStateRector\Fixture;

$context = new \Shopware\Core\Framework\Context();
$context->addExtension(\Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry::USE_INDEXING_QUEUE, new \Shopware\Core\Framework\Struct\ArrayEntity());
?>
-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\ContextMetadataExtensionToStateRector\Fixture;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;

$context = new Context();
$context->addState(EntityIndexerRegistry::USE_INDEXING_QUEUE);
?>
