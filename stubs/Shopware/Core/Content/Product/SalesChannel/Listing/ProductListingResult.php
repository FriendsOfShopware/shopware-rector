<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\SalesChannel\Listing;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ProductListingResult
{
    public function getEntities(): EntityCollection
    {
        return new EntityCollection();
    }
}
