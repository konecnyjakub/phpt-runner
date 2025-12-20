--TEST--
Flaky test
--FILE--
<?php
echo rand(0, 1);
?>
--EXPECT--
1
--FLAKY--
This is random
