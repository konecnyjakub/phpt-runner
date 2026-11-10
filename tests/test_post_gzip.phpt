--TEST--
Test post gzip
--FILE--
<?php echo count($_POST); ?>
--EXPECT--
1
--GZIP_POST--
one=abc&two[]=def&two[]=ghi
