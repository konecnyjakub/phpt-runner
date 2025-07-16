--TEST--
Test capture stdio
--FILE--
<?php
echo "test123";
fwrite(STDERR, "test error");
?>
--EXPECT--
test error
--CAPTURE_STDIO--
STDIN STDERR
