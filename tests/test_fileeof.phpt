--TEST--
Test fileeof
--FILE--
<?php
echo "test123";
//last line comment

--EXPECT--
test123
