<?php
header("Content-type: text/plain");
echo md5(file_get_contents($_FILES['thumbnail']['tmp_name']));
?>
