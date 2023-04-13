<?php
	$images = glob('./*.{jpg,jpeg,png,gif}', GLOB_BRACE);
	$image = $images[array_rand($images)];
	header('Content-Type: image/jpg');
	echo file_get_contents($image);
?>