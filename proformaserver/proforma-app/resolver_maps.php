<?php
// resolver_maps.php - "El Detective de Links"

header('Content-Type: application/json');

$url = $_GET['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'message' => 'No se recibió URL']);
    exit;
}

// 1. Limpieza básica de la URL
$url = filter_var($url, FILTER_SANITIZE_URL);

// 2. Función para seguir redirecciones (El truco)
function obtenerUrlFinal($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecciones
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // No descargar el cuerpo, solo cabeceras
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'); // Parecer un navegador
    
    curl_exec($ch);
    $urlFinal = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    return $urlFinal;
}

$urlExpandida = obtenerUrlFinal($url);

// 3. Buscar coordenadas en la URL final usando Expresiones Regulares
$lat = null;
$lng = null;

// CASO A: Formato @lat,lon (El más común en escritorio)
if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $urlExpandida, $matches)) {
    $lat = $matches[1];
    $lng = $matches[2];
}
// CASO B: Formato q=lat,lon (Común en búsqueda)
elseif (preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $urlExpandida, $matches)) {
    $lat = $matches[1];
    $lng = $matches[2];
}
// CASO C: Formato !3dlat!4dlon (Datos incrustados de Google)
elseif (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $urlExpandida, $matches)) {
    $lat = $matches[1];
    $lng = $matches[2];
}
// CASO D: Buscar ll= (A veces usado en APIs antiguas)
elseif (preg_match('/ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $urlExpandida, $matches)) {
    $lat = $matches[1];
    $lng = $matches[2];
}

// 4. Responder
if ($lat && $lng) {
    echo json_encode([
        'success' => true,
        'lat' => $lat,
        'lng' => $lng,
        'url_final' => $urlExpandida
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudieron extraer coordenadas.',
        'debug_url' => $urlExpandida
    ]);
}
?>