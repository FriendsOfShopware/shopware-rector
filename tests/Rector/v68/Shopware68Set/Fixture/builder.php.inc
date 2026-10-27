<?php

function enrich(\Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface $builder, $criteria, $context, $id): void
{
    $criteria->addFilter(...$builder->buildFilters($id, $context));
}
-----
<?php

function enrich(\Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder $builder, $criteria, $context, $id): void
{
    $builder->enrichCriteria($criteria, $id, $context);
}
