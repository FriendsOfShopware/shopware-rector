<?php

class Foo {
    public function __construct(array $arg1, array $arg2 = null)
    {
    }
}

new Foo([])

?>

-----
<?php

class Foo {
    public function __construct(array $arg1, array $arg2)
    {
    }
}

new Foo([], null)

?>

