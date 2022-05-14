<?php

abstract class AbstractCaptcha
{
    public function supports(string $test)
    {
    }
}

class SomeImpl extends AbstractCaptcha
{
    public function supports(string $test)
    {
    }
}
?>

-----
<?php

abstract class AbstractCaptcha
{
    public function supports(string $test, array $captcha)
    {
    }
}

class SomeImpl extends AbstractCaptcha
{
    public function supports(string $test, array $captcha)
    {
    }
}
?>

