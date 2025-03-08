<?php
// Set content type
header('Content-Type: image/png');

// Create image
$width = 32;
$height = 32;
$image = imagecreatetruecolor($width, $height);

// Set background color (blue)
$bg_color = imagecolorallocate($image, 0, 102, 204); // #0066cc

// Make the background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Set text color (white)
$text_color = imagecolorallocate($image, 255, 255, 255);

// Add text
imagestring($image, 5, 5, 9, "B&H", $text_color);

// Output image
imagepng($image);

// Free up memory
imagedestroy($image);
?>