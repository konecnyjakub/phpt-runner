--TEST--
Test put
--FILE--
<?php $input = json_decode(file_get_contents("php://input")); var_dump($input instanceof stdClass && $input->field1 === "abc" && $input->field2 === "def"); ?>
--EXPECT--
bool(true)
--PUT--
Content-type: application/json

{"field1": "abc", "field2": "def"}
