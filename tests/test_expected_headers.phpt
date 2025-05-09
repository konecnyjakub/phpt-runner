--TEST--
Test headers
--FILE--
<?php
header("Content-type: text/plain; charset=UTF-8");
header("Pragma: no-cache");
echo "test123";
?>
--EXPECT--
test123
--EXPECTHEADERS--
Content-type: text/plain; charset=UTF-8
Pragma: no-cache
