<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Search;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class EntitySearchResult
{
    public function getEntities(): EntityCollection
    {
        return new EntityCollection();
    }
}
