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

    public function changedVariable(string $streamId, string $otherStreamId, Context $context): Criteria
    {
        $filters = $this->productStreamBuilder->buildFilters($streamId, $context);
        $streamId = $otherStreamId;
        $criteria = new Criteria();
        $criteria->addFilter(...$filters);

        return $criteria;
    }

    public function changedContext(string $streamId, Context $context): Criteria
    {
        $filters = $this->productStreamBuilder->buildFilters($streamId, $context);
        $context->addState('some-state');
        $criteria = new Criteria();
        $criteria->addFilter(...$filters);

        return $criteria;
    }

    public function earlyReturn(string $streamId, Context $context, bool $skip): Criteria
    {
        $filters = $this->productStreamBuilder->buildFilters($streamId, $context);
        $criteria = new Criteria();
        if ($skip) {
            return $criteria;
        }
        $criteria->addFilter(...$filters);

        return $criteria;
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

    public function changedVariable(string $streamId, string $otherStreamId, Context $context): Criteria
    {
        $productStreamFilterCriteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $this->productStreamBuilder->enrichCriteria($productStreamFilterCriteria, $streamId, $context);
        $filters = array_values($productStreamFilterCriteria->getFilters());
        $streamId = $otherStreamId;
        $criteria = new Criteria();
        $criteria->addFilter(...$filters);

        return $criteria;
    }

    public function changedContext(string $streamId, Context $context): Criteria
    {
        $productStreamFilterCriteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $this->productStreamBuilder->enrichCriteria($productStreamFilterCriteria, $streamId, $context);
        $filters = array_values($productStreamFilterCriteria->getFilters());
        $context->addState('some-state');
        $criteria = new Criteria();
        $criteria->addFilter(...$filters);

        return $criteria;
    }

    public function earlyReturn(string $streamId, Context $context, bool $skip): Criteria
    {
        $productStreamFilterCriteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $this->productStreamBuilder->enrichCriteria($productStreamFilterCriteria, $streamId, $context);
        $filters = array_values($productStreamFilterCriteria->getFilters());
        $criteria = new Criteria();
        if ($skip) {
            return $criteria;
        }
        $criteria->addFilter(...$filters);

        return $criteria;
    }
}
