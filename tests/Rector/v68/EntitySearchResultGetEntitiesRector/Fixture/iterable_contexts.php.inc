<?php

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

function iterableContexts(EntitySearchResult $result): Generator
{
    $entities = [...$result];
    consume(...$result);
    yield from $result;
}
-----
<?php

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

function iterableContexts(EntitySearchResult $result): Generator
{
    $entities = [...$result->getEntities()];
    consume(...$result->getEntities());
    yield from $result->getEntities();
}
