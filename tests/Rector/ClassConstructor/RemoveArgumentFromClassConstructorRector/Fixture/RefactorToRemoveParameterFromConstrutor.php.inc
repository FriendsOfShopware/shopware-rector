<?php
$someObject = new SomeExampleClass($example);
$someOtherObject = new SomeOtherExampleClass($example, $example2);
$someMoreAdvancedObject = new SomeMoreAdvancedExampleClass($example, $example2, $example3);
$removeFirstArgumentObject = new RemoveFirstArgument($example, $example2, $example3);
?>

-----
<?php
$someObject = new SomeExampleClass();
$someOtherObject = new SomeOtherExampleClass($example);
$someMoreAdvancedObject = new SomeMoreAdvancedExampleClass($example, $example3);
$removeFirstArgumentObject = new RemoveFirstArgument($example2, $example3);
?>

