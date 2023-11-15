<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateCaptchaAnnotationToRoute\Fixture;

use Shopware\Storefront\Framework\Captcha\Annotation\Captcha;
use Symfony\Component\Routing\Annotation\Route;

class RefactorWithDefaultsSetForRoute
{
    /**
     * @Route("/form/contact", name="frontend.form.contact.send", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     * @Captcha
     */
    public function sendContactForm()
    {
    }
}
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateCaptchaAnnotationToRoute\Fixture;

use Shopware\Storefront\Framework\Captcha\Annotation\Captcha;
use Symfony\Component\Routing\Annotation\Route;

class RefactorWithDefaultsSetForRoute
{
    /**
     * @Route("/form/contact", name="frontend.form.contact.send", methods={"POST"}, defaults={"XmlHttpRequest"=true, "_captcha"=true})
     */
    public function sendContactForm()
    {
    }
}
?>

