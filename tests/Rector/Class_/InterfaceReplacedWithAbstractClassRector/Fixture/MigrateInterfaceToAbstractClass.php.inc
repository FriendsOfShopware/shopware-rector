<?php

interface CartFoo
{

}

abstract class AbstractCartFoo
{

}

class CartImplt implements CartFoo
{

}

?>

-----
<?php

interface CartFoo
{

}

abstract class AbstractCartFoo
{

}

class CartImplt extends AbstractCartFoo
{
}

?>

