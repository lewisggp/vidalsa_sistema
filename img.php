<?php
$file = __DIR__.'/public/images/maquinaria/logo.webp';
if(file_exists($file)){
    $im = @imagecreatefromwebp($file);
    if($im) {
        $out = __DIR__.'/public/images/maquinaria/logo.png';
        imagepng($im, $out);
        imagedestroy($im);
        echo "Exito";
    } else {
        echo "No se pudo leer";
    }
} else {
    echo "No existe";
}
