<?php declare(strict_types=1);

namespace Shopware\Elasticsearch\Framework;

use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

abstract class AbstractElasticsearchDefinition
{
    abstract public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery;
}
