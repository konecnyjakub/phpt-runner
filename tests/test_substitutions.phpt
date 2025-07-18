--TEST--
Test substitutions
--FILE--
<?php echo "+123 abc test"; ?>
--EXPECTF--
%i%w%s test
