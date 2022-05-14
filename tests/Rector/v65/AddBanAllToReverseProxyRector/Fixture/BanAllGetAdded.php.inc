<?php
namespace Frosh\Rector\Tests\Rector\v65\AddBanAllToReverseProxyRector\Fixture;

class Test extends \Shopware\Storefront\Framework\Cache\ReverseProxy\AbstractReverseProxyGateway {

}
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\AddBanAllToReverseProxyRector\Fixture;

use Shopware\Storefront\Framework\Cache\ReverseProxy\AbstractReverseProxyGateway;
class Test extends AbstractReverseProxyGateway
{
    public function banAll()
    {
        $this->ban(['/']);
    }
}
?>
