--TEST--
Test post deflate
--FILE--
<?php echo count($_POST); ?>
--EXPECT--
1
--DEFLATE_POST--
one=abc&two[]=def&two[]=ghi
