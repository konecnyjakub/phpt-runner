--TEST--
Conflicting test
--FILE--
<?php
echo "test123";
?>
--EXPECT--
test123
--CONFLICTS--
one
two
