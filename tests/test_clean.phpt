--TEST--
Test clean
--FILE--
<?php
echo "test123";
touch(__DIR__ . DIRECTORY_SEPARATOR . "tmp1.txt");
?>
--EXPECT--
test123
--CLEAN--
<?php unlink(__DIR__ . DIRECTORY_SEPARATOR . "tmp1.txt") ?>
