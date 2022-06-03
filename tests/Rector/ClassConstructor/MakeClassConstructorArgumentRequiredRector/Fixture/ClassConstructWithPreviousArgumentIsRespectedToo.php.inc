<?php

class Bar {
    public function __construct(array $arg1, array $arg2 = null)
    {
    }
}

$someVariable = ['someValue'];
new Bar([], $someVariable)

?>

-----
<?php

class Bar {
    public function __construct(array $arg1, array $arg2)
    {
    }
}

$someVariable = ['someValue'];
new Bar([], $someVariable)

?>

