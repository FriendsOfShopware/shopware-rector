<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateLoginRequiredAnnotationToRouteRector\Fixture;

use Shopware\Core\Framework\Routing\Annotation\LoginRequired;
use Symfony\Component\Routing\Annotation\Route;

class Foo
{
    /**
     * @LoginRequired
     * @Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"})
     */
    public function myAction()
    {
    }
}
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateLoginRequiredAnnotationToRouteRector\Fixture;

use Shopware\Core\Framework\Routing\Annotation\LoginRequired;
use Symfony\Component\Routing\Annotation\Route;

class Foo
{
    /**
     * @Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"}, defaults={"_loginRequired"=true})
     */
    public function myAction()
    {
    }
}
?>

