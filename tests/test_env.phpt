--TEST--
Test env
--FILE--
<?php echo getenv("one"); ?>
--EXPECT--
abc
--ENV--
one=abc
