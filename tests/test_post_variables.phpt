--TEST--
Test post variables
--FILE--
<?php echo $_POST["two"][1]; ?>
--EXPECT--
ghi
--POST--
one=abc&two[]=def&two[]=ghi
