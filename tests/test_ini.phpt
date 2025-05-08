--TEST--
Test ini
--FILE--
<?php echo ini_get("allow_url_fopen"); ?>
--EXPECT--
0
--INI--
allow_url_fopen=0
