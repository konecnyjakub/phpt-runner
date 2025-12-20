--TEST--
Test skip flaky
--SKIPIF--
<?php echo "flaky"; ?>
--FILE--
<?php echo rand(0, 1); ?>
--EXPECT--
1
