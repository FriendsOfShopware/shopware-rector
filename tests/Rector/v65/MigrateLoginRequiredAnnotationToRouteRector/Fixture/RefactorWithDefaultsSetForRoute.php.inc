<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateLoginRequiredAnnotationToRouteRector\Fixture;

use Shopware\Core\Framework\Routing\Annotation\LoginRequired;
use Symfony\Component\Routing\Annotation\Route;

class RefactorWithDefaultsSetForRoute
{
    /**
     * @LoginRequired
     * @Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"}, defaults={"foo"=1})
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

class RefactorWithDefaultsSetForRoute
{
    /**
     * @Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"}, defaults={"foo"=1, "_loginRequired"=true})
     */
    public function myAction()
    {
    }
}
?>

