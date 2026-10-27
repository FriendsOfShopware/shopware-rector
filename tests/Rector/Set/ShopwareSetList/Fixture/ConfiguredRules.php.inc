<?php
namespace Frosh\Rector\Tests\Rector\Set\ShopwareSetList\Fixture;

use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\JsonFieldSerializer;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;

function run(CalculatedTaxCollection $taxes, CalculatedTaxCollection $other): void
{
    $taxes->merge($other, true);
    JsonFieldSerializer::encodeJson(['foo' => 'bar']);

    throw new MissingRequestParameterException('id');
}
?>
-----
<?php
namespace Frosh\Rector\Tests\Rector\Set\ShopwareSetList\Fixture;

use Shopware\Core\Framework\Util\Json;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\JsonFieldSerializer;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;

function run(CalculatedTaxCollection $taxes, CalculatedTaxCollection $other): void
{
    $taxes->merge($other);
    Json::encode(['foo' => 'bar']);

    throw RoutingException::missingRequestParameter('id');
}
?>
