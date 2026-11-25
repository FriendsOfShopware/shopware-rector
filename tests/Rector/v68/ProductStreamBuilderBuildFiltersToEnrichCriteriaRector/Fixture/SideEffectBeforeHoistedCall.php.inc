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

    public function doSomething(string $streamId, string $otherStreamId, Context $context): void
    {
        $this->consume($streamId = $otherStreamId, $this->productStreamBuilder->buildFilters($streamId, $context));
        $this->consume($this->prepare($context), $this->productStreamBuilder->buildFilters($streamId, $context));
        $this->consume($this->productStreamBuilder->buildFilters($streamId, $context), $this->prepare($context));
    }

    private function consume(mixed ...$values): void
    {
    }

    private function prepare(Context $context): string
    {
        return '';
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

    public function doSomething(string $streamId, string $otherStreamId, Context $context): void
    {
        // TODO: Replace buildFilters() call with AbstractProductStreamBuilder::enrichCriteria() - please check manually
        $this->consume($streamId = $otherStreamId, $this->productStreamBuilder->buildFilters($streamId, $context));
        // TODO: Replace buildFilters() call with AbstractProductStreamBuilder::enrichCriteria() - please check manually
        $this->consume($this->prepare($context), $this->productStreamBuilder->buildFilters($streamId, $context));
        $productStreamFilterCriteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $this->productStreamBuilder->enrichCriteria($productStreamFilterCriteria, $streamId, $context);
        $this->consume(array_values($productStreamFilterCriteria->getFilters()), $this->prepare($context));
    }

    private function consume(mixed ...$values): void
    {
    }

    private function prepare(Context $context): string
    {
        return '';
    }
}
