<?php

function filters(\Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface $builder, $criteria, $id, $context): void
{
    $filters = $builder->buildFilters($id, $context);
    $criteria->addFilter(...$filters);
    logFilters($filters);
    $criteria->addFilter($other, ...$builder->buildFilters($id, $context));
    $criteria->addFilter(...$builder->buildFilters(context: $context, id: $id));
}
-----
<?php

use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;

function filters(ProductStreamBuilderInterface $builder, $criteria, $id, $context): void
{
    // TODO: Replace buildFilters() call with AbstractProductStreamBuilder::enrichCriteria() - please check manually
    $filters = $builder->buildFilters($id, $context);
    $criteria->addFilter(...$filters);
    logFilters($filters);
    // TODO: Replace buildFilters() call with AbstractProductStreamBuilder::enrichCriteria() - please check manually
    $criteria->addFilter($other, ...$builder->buildFilters($id, $context));
    $builder->enrichCriteria($criteria, context: $context, id: $id);
}
