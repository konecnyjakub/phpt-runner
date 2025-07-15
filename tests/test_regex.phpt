--TEST--
Test regex
--FILE--
<?php echo "test123"; ?>
--EXPECTREGEX--
test[0-9]+
