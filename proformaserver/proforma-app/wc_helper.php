<?php
// wc_helper.php

function wc_get_products_simple($ck, $cs, $store_url, $per_page=50) {
    // Construimos la URL
    $url = rtrim($store_url, '/') . "/wp-json/wc/v3/products?per_page=$per_page";
    $url .= "&consumer_key=" . urlencode($ck) . "&consumer_secret=" . urlencode($cs);

    // Inicializamos CURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Pon en false si tienes problemas con SSL local
    
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($info['http_code'] != 200) {
        return []; 
    }

    $arr = json_decode($res, true);
    if (!is_array($arr)) return [];

    $simple = [];
    foreach($arr as $p) {
        // Obtenemos las imágenes (si existen)
        $img1 = isset($p['images'][0]['src']) ? $p['images'][0]['src'] : '';
        $img2 = isset($p['images'][1]['src']) ? $p['images'][1]['src'] : '';

        // 2. BUSCAMOS EL MODELO EN LOS ATRIBUTOS (NUEVO)
        $modelo_encontrado = '';
        if (!empty($p['attributes'])) {
            foreach ($p['attributes'] as $attr) {
                // Buscamos si el atributo se llama "Modelo" o "Model" (sin importar mayúsculas)
                if (strcasecmp($attr['name'], 'Modelo') === 0 || strcasecmp($attr['name'], 'Model') === 0) {
                    // Tomamos el primer valor (ej: HQ-YL1000)
                    $modelo_encontrado = $attr['options'][0] ?? ''; 
                    break;
                }
            }
        }

        // Guardamos todo lo que necesitamos
        $simple[] = [
            'id'    => $p['id'],
            'name'  => $p['name'],
            'price' => $p['price'],
            'sku'   => $p['sku'],
            'desc'  => strip_tags($p['short_description']), // Quitamos HTML
            'img1'  => $img1,
            'img2'  => $img2,
            'model' => $modelo_encontrado
        ];
    }
    return $simple;
}
?>