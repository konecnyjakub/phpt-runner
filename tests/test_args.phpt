--TEST--
Test args
--ARGS--
--one=abc --two def
--FILE--
<?php var_dump($argv[1] === "--one=abc" && $argv[2] === "--two" && $argv[3] === "def"); ?>
--EXPECT--
bool(true)
