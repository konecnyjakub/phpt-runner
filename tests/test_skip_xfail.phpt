--TEST--
Test skip xfail
--SKIPIF--
<?php echo "xfail"; ?>
--FILE--
<?php echo "one"; ?>
--EXPECT--
two
