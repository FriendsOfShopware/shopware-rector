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

    public function mergeFilters(string $streamId, Context $context): array
    {
        $productStreamFilterCriteria = $this->loadDefaultFilters();

        return [...$productStreamFilterCriteria, ...$this->productStreamBuilder->buildFilters($streamId, $context)];
    }

    public function combineStreams(string $streamId, string $otherStreamId, Context $context): array
    {
        return array_merge(
            $this->productStreamBuilder->buildFilters($streamId, $context),
            $this->productStreamBuilder->buildFilters($otherStreamId, $context),
        );
    }

    private function loadDefaultFilters(): array
    {
        return [];
    }
}
-----
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

    public function mergeFilters(string $streamId, Context $context): array
    {
        $productStreamFilterCriteria = $this->loadDefaultFilters();
        $productStreamFilterCriteria2 = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $this->productStreamBuilder->enrichCriteria($productStreamFilterCriteria2, $streamId, $context);

        return [...$productStreamFilterCriteria, ...array_values($productStreamFilterCriteria2->getFilters())];
    }

    public function combineStreams(string $streamId, string $otherStreamId, Context $context): array
    {
        $productStreamFilterCriteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $this->productStreamBuilder->enrichCriteria($productStreamFilterCriteria, $streamId, $context);
        $productStreamFilterCriteria2 = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $this->productStreamBuilder->enrichCriteria($productStreamFilterCriteria2, $otherStreamId, $context);
        return array_merge(
            array_values($productStreamFilterCriteria->getFilters()),
            array_values($productStreamFilterCriteria2->getFilters()),
        );
    }

    private function loadDefaultFilters(): array
    {
        return [];
    }
}
