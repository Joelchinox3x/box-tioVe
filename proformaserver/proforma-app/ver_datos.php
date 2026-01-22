<?php
// ARCHIVO: ver_datos.php
require 'config.php'; // Necesitamos tus claves

// Configuración rápida
$ver_cuantos = 1; // Solo traemos 1 para no marearnos

// Construimos la URL
$url = rtrim($wc_store_url, '/') . "/wp-json/wc/v3/products?per_page=" . $ver_cuantos;
$url .= "&consumer_key=" . urlencode($wc_consumer_key) . "&consumer_secret=" . urlencode($wc_consumer_secret);

// Conectamos
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignorar SSL en local
$response = curl_exec($ch);
curl_close($ch);

// Convertimos a Array
$productos = json_decode($response, true);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inspector de WooCommerce</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
        .box { background: #2d2d2d; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #444; }
        h2 { color: #569cd6; border-bottom: 1px solid #444; padding-bottom: 10px; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        .highlight { color: #ce9178; font-weight: bold; }
        .key { color: #9cdcfe; }
    </style>
</head>
<body>

    <h1>🕵️ Inspector de Datos WooCommerce</h1>
    <p>Viendo el último producto agregado...</p>

    <?php if (empty($productos)): ?>
        <div class="box" style="border-color: red;">
            <h2>❌ Error</h2>
            <p>No se recibieron datos. Revisa tus credenciales en config.php o tu conexión.</p>
        </div>
    <?php else: ?>

        <?php foreach($productos as $p): ?>
            <div class="box">
                <h2>📦 Producto: <?= $p['name'] ?> (ID: <?= $p['id'] ?>)</h2>
                
                <div style="background: #3c3c3c; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <h3 style="margin-top:0; color: #dcdcaa;">🔍 LUPA EN ATRIBUTOS (Busca tu modelo aquí):</h3>
                    <?php if(empty($p['attributes'])): ?>
                        <p style="color: #f44747;">Este producto no tiene atributos configurados en WooCommerce.</p>
                    <?php else: ?>
                        <pre><?php print_r($p['attributes']); ?></pre>
                    <?php endif; ?>
                </div>

                <h3>📄 Datos Crudos Completos:</h3>
                <pre><?php print_r($p); ?></pre>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</body>
</html>