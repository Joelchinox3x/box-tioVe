<?php
// backend/public/check_alpha.php
header('Content-Type: text/plain');
$dir = __DIR__ . '/../files/card_templates/borders/';
$files = glob($dir . '*.png');

if (empty($files)) {
    echo "No PNG files found in $dir\n";
    exit;
}

foreach ($files as $file) {
    $filename = basename($file);
    echo "Checking $filename ... ";
    
    if (!function_exists('imagecreatefrompng')) {
        echo "Error: GD library not installed.\n";
        break;
    }

    $img = @imagecreatefrompng($file);
    if (!$img) { 
        echo "Failed to load image.\n"; 
        continue; 
    }
    
    // Check if image is truecolor
    if (!imageistruecolor($img)) {
         echo "Not TrueColor. ";
    } else {
         echo "TrueColor. ";
    }
    
    // Save Alpha flag
    if (!imageistruecolor($img)) {
        // Paletted
    }

    // Check corners and center for transparency
    $hasTransparent = false;
    $width = imagesx($img);
    $height = imagesy($img);
    
    $points = [
        [0, 0], 
        [$width-1, 0], 
        [0, $height-1], 
        [$width-1, $height-1],
        [round($width/2), round($height/2)]
    ];

    foreach ($points as $p) {
        $rgb = imagecolorat($img, $p[0], $p[1]);
        $colors = imagecolorsforindex($img, $rgb);
        // Alpha: 0 (opaque) to 127 (transparent) in GD
        echo " [({$p[0]},{$p[1]}) A:{$colors['alpha']}]";
        if ($colors['alpha'] > 10) { 
            $hasTransparent = true;
        }
    }

    if ($hasTransparent) {
        echo " => HAS TRANSPARENCY.\n";
    } else {
        echo " => OPAQUE.\n";
    }

    imagedestroy($img);
}
?>
