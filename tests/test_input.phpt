--TEST--
Test input
--FILE--
<?php echo stream_get_contents(STDIN); ?>
--EXPECT--
first line
second line
--STDIN--
first line
second line
