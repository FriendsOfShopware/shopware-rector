<?php

function reviews(\Shopware\Core\Content\Product\SalesChannel\Review\ProductReviewResult $reviews, \Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult $listing): void
{
    $reviews?->first();
    foreach ($reviews as $review) {}
    count($reviews);
    $listing->clear();
    $listing->getIterator();
    $listing->setPage(2);
}
-----
<?php

use Shopware\Core\Content\Product\SalesChannel\Review\ProductReviewResult;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;

function reviews(ProductReviewResult $reviews, ProductListingResult $listing): void
{
    $reviews?->getEntities()->first();
    foreach ($reviews->getEntities() as $review) {}
    count($reviews->getEntities());
    $listing->getEntities()->clear();
    $listing->getEntities()->getIterator();
    $listing->setPage(2);
}
