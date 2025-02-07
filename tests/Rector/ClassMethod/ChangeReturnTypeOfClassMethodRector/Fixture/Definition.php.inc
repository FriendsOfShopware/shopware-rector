<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\ClassMethod\ChangeReturnTypeOfClassMethodRector\Fixture;

use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;

class ElasticsearchProductDefinition extends AbstractElasticsearchDefinition
{
    public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery
    {
    }
}

-----
<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\ClassMethod\ChangeReturnTypeOfClassMethodRector\Fixture;

use OpenSearchDSL\BuilderInterface;
use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;

class ElasticsearchProductDefinition extends AbstractElasticsearchDefinition
{
    public function buildTermQuery(Context $context, Criteria $criteria): BuilderInterface
    {
    }
}
