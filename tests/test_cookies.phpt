--TEST--
Test cookies
--FILE--
<?php echo $_COOKIE["one"]; ?>
--EXPECT--
abc
--COOKIE--
one=abc;two=def
one=def
