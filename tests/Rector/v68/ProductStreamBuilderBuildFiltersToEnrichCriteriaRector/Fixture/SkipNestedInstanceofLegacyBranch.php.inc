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

    public function doSomething(?string $streamId, Context $context): Criteria
    {
        $criteria = new Criteria();
        if ($this->productStreamBuilder instanceof AbstractProductStreamBuilder) {
            $this->productStreamBuilder->enrichCriteria($criteria, $streamId, $context);
        } else {
            if ($streamId !== null) {
                try {
                    $criteria->addFilter(...$this->productStreamBuilder->buildFilters($streamId, $context));
                } catch (\Throwable) {
                }
            }

            foreach ([$streamId] as $id) {
                $filters = $this->productStreamBuilder->buildFilters($id, $context);
            }

            $load = function () use ($streamId, $context): array {
                return $this->productStreamBuilder->buildFilters($streamId, $context);
            };
        }

        return $criteria;
    }
}
