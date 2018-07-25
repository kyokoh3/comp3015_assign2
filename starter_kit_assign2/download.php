<?php

$file_name = $_GET['filename'];
$file_url = 'uploads/' . $file_name;

header("Content-Type: image/jpeg;");
header("Content-disposition: attachment; filename=\"".$file_name."\"");
$copy = imagecreatefromjpeg($file_url); 
imagejpeg($copy);

imagedestroy($copy);

?>