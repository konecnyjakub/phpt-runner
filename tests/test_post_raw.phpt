--TEST--
Test post raw
--FILE--
<?php var_dump($_POST["field1"] === "abc" && is_uploaded_file($_FILES["field2"]["tmp_name"])); ?>
--EXPECT--
bool(true)
--POST_RAW--
Content-type: multipart/form-data, boundary=AaB03x

--AaB03x
content-disposition: form-data; name="field1"

abc
--AaB03x
content-disposition: form-data; name="field2"; filename="test.txt"
Content-Type: text/plain

abcdef123456789
--AaB03x--
