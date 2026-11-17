<?php

namespace Shopware\Core\Content\ProductStream\Service;

interface ProductStreamBuilderInterface
{
    /** @return array<int, mixed> */
    public function buildFilters(string $id, \Shopware\Core\Framework\Context $context): array;
}

abstract class AbstractProductStreamBuilder implements ProductStreamBuilderInterface
{
}

namespace MyPlugin;

use Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class MyService
{
    public function __construct(
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
    ) {
    }

    public function doSomething(string $streamId, Context $context, Criteria $criteria): void
    {
        if ($this->productStreamBuilder instanceof AbstractProductStreamBuilder) {
            $this->productStreamBuilder->enrichCriteria($criteria, $streamId, $context);
        } else {
            $criteria->addFilter(...$this->productStreamBuilder->buildFilters($streamId, $context));
        }

        if ($this->productStreamBuilder instanceof ProductStreamBuilderInterface) {
            $filters = $this->productStreamBuilder->buildFilters($streamId, $context);
        }
    }
}
