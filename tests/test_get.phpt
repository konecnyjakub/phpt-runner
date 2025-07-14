--TEST--
Test get
--FILE--
<?php echo $_GET["two"][1]; ?>
--EXPECT--
ghi
--GET--
one=abc&two[]=def&two[]=ghi
one=def
