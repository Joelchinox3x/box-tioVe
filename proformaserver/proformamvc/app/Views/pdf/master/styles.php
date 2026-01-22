<?php
// styles.php 
// Asegurar que las variables existan, extrayéndolas de $fullData si es necesario
$color_brand = $fullData['color_brand'] ?? $color_brand ?? "#f37021";
$fondo_img   = $fullData['fondo_img']   ?? $fondo_img   ?? "";
$color_text  = "#333333";
$color_bg_total = "#eeeeee";
?>


/* GENERAL */

body { 
    font-family: sans-serif; 
    font-size: 11px; 
    color: <?= $color_text ?>; 
    line-height: 1.2;
    
    /* --- LÓGICA DEL FONDO --- */
    background-image: url('<?= $fondo_img ?>');
    background-repeat: no-repeat;
    background-position: center center;
    
    /* PROPIEDAD MÁGICA DE MPDF:                    
       4 = Resize to fit (mantiene proporción)
       5 = Resize to fit box
       6 = Stretch to page (Estirar a toda la hoja A4, ideal para plantillas) 
    */
    background-image-resize: 6; 
}

/* Clases de utilidad usando la variable PHP */
.text-brand { color: <?= $color_brand ?>; font-weight: bold;   }
.bg-brand { background-color: <?= $color_brand ?>; color: white; }
/*  .border-brand { border: 1px solid <?= $color_brand ?>; } */ 

table { width: 100%; border-collapse: separate; border-spacing: 0; } 
td { vertical-align: top; }

/* LOGO Y HEADER */
.logo { width: 130px; height: auto; display: block; }

.caja-logo {
   /* Contenedor externo posicionado o marginado */
}

.caja-logo-cont {
    width: 140px;
    height: 140px;
    border: 2px solid #ffffffff;
    background-color: #FFCC00;
    border-radius: 50%;
    text-align: center; /* Centra horizontalmente elementos inline */
    margin-right: auto;
}

.logo2 { 
    display: inline-block; /* Necesario para text-align y vertical-align */
    vertical-align: middle; /* Se alinea con el line-height del padre */
    
    width: auto;
    height: auto; 
    max-width: 80%;
    max-height: 80%;
}

.logo-cell { vertical-align: middle; text-align: center; }
.logo-cell2 { vertical-align: left; text-align: left; }

.titulo-principal {
    font-family: '<?= $fullData['template_config']['title_font'] ?? 'sans-serif' ?>', sans-serif;
    font-size: <?= $fullData['template_config']['title_size'] ?? 31 ?>px;
    font-weight: <?= ($fullData['template_config']['title_bold'] ?? 1) ? 'bold' : 'normal' ?>;
    text-transform: uppercase;
    color: <?= $fullData['template_config']['title_color'] ?? '#000000' ?>; 
    margin-bottom: 5px; 
    letter-spacing: -0.4px; 
    line-height: 0.9; 
    white-space: nowrap;
}

.titulo-principal_blue {
    font-family: 'dynapuff', sans-serif;
    font-size: 42px; /* Ajuste posible si la fuente es muy grande/pequeña */
    font-weight: bold;
    color: #444;       
    margin-bottom: 5px;
    letter-spacing: 0px; /* DynaPuff suele ser ancha */
    line-height: 1.1; 
}
.linea-slogan { font-size: 18px; font-weight: bold; margin-bottom: 2px; }

.celda-centro { width: 59%; text-align: center; vertical-align: top; padding: 0; }
.info-empresa { font-size: 12px; }
.linea-info-bold { font-weight: bold; margin-bottom: 2px; }
.linea-info { margin-bottom: 2px; }
.v-middle { vertical-align: middle; }
 
.caja-ruc {
    width: 23%;
    border: 2px solid <?= $color_brand ?>; /* CAMBIO DINAMICO */
    border-radius: 18px;
    overflow: hidden;
    text-align: center;
    margin-left: auto;
    margin-top: 5px;
    font-family: Tahoma, sans-serif;
}

/* Estilos anteriores (tabla) */
.caja-ruc table { width: 140%; border-collapse: collapse; text-align: center; }
.fila-ruc-numero { background-color: <?= $color_bg_total ?>; font-size: 27px; height: 45px; font-weight: bold; vertical-align: middle; }
.fila-label { background-color: <?= $color_brand ?>; color: white; font-size: 27px; height: 50px; font-weight: bold; vertical-align: middle; letter-spacing: 1px; }
.fila-codigo { font-size: 22px; color: #333; height: 45px; font-weight: bold; vertical-align: middle; }

/* NUEVOS ESTILOS DIV (caja-ruc-div) */
.caja-ruc-div {
    width: 23%;
    border: 2px solid <?= $color_brand ?>;
    border-radius: 18px;
    text-align: center;
    margin-left: auto;
    font-family: Tahoma, sans-serif;
    /* overflow: hidden;  <-- mPDF a veces lo ignora, asi que usamos radius internos */
}

.ruc-div-top {
    background-color: <?= $color_bg_total ?>;
    font-size: 20px;
    height: 32px; /* Reducido de 45px */
    line-height: 32px; 
    font-weight: bold;
    border-top-left-radius: 14px; 
    border-top-right-radius: 14px;
}

.ruc-div-mid {
    background-color: <?= $color_brand ?>;
    color: white;
    font-size: 22px;
    height: 34px; /* Reducido de 50px */
    line-height: 34px;
    font-weight: bold;
    letter-spacing: 1px;
}

.ruc-div-bot {
    background-color: <?= $color_bg_total ?>;
    font-size: 18px;
    color: #333;
    height: 32px; /* Reducido de 45px */
    line-height: 32px;
    font-weight: bold;
    border-bottom-left-radius: 14px;
    border-bottom-right-radius: 14px;
}

.msj-respeto { margin-top: 15px; font-size: 12px; }
/* CAJA CLIENTE (HOJA 1) */
.client-box {
    margin-top: 10px;
    border: 1px solid <?= $color_brand ?>;
    padding: 10px;
    border-radius: 20px;
}
.client-table td { padding: 2px; font-size: 11px; }

/* RESUMEN ITEMS */
.summary-header {
    margin-top: 15px;
    border: 1px solid <?= $color_brand ?>;
    color: <?= $color_brand ?>;
    text-align: center;
    font-weight: bold;
    padding: 6px;
    font-size: 19px;
    text-transform: uppercase;
    border-bottom: 0; /* Se une con la tabla */
}

.items-table { border-collapse: collapse; width: 100%; }
.items-table th { 
    background-color: <?= $color_brand ?>; 
    color: white; 
    padding: 8px; 
    border-right: 1px solid #eee; 
    border-left: 1px solid <?= $color_brand ?>;
    font-size: 13px;
}
.items-table td { 
    border: 1px solid #ababab; 
  
    padding: 8px; 
    vertical-align: middle; 
    font-size: 12px;
}


/* TOTALES */
.total-label { border-bottom: 1px solid white; background-color: <?= $color_brand ?>; color: white; font-weight: bold; padding: 8px; }
.total-value { border-bottom: 1px solid white; background-color: <?= $color_bg_total ?>; font-weight: bold; text-align: center; padding: 8px; }

/* CONDICIONES */
.terms-header { 
    background-color: <?= $color_brand ?>; 
    color: white; 
    padding: 8px; 
    margin-top: 20px; 
    font-size: 13px; 
    font-weight: bold; 
    border: 1px solid <?= $color_brand ?>;
}
.terms-body { border: 1px solid <?= $color_brand ?>; padding: 0px; border-top: 0; }

/* FOOTER BANCOS */
.contact-table { margin-top: 20px; border-collapse: collapse; width: 100%; font-size: 11px; }
.contact-header { background-color: <?= $color_brand ?>; color: white; padding: 8px; font-weight: bold; font-size: 13px }
.contact-body { border: 1px solid <?= $color_brand ?>; padding: 8px; vertical-align: middle; line-height: 2;}

.banco-tabla { width: 100%; border-collapse: collapse; margin-top: 0px; }
.banco-tabla td { border: 1px solid #ffffff; padding: 3px 0px; font-size: 12px; }

.banco-logo { text-align: center; }

.banco-bcp { color:#023c7d; font-weight: bold; white-space: nowrap; }
.banco-bbva { color:#004481; font-weight: bold; white-space: nowrap; }
.banco-numero { text-align: left; padding-left: 10px; white-space: nowrap; }
.banco-cci { text-align: left; padding-left: 10px; white-space: nowrap; color: #000000; }

.tabla-detalles { border: 1px solid #aaa; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
.tabla-detalles th { background: #eee; font-weight: bold; border: 1px solid #aaa; text-align: center; padding: 4px; }
.tabla-detalles td { border: 1px solid #ccc; padding: 4px; }



/* --- ESTILOS FICHA TÉCNICA (HOJA 2) --- */

.specs-title { 
    background-color: <?= $color_brand ?>; 
    color: white; 
    padding: 8px; 
    font-weight: bold; 
    border-radius: 5px 5px 0 0; 
    font-size: 14px; 
    margin-top: 20px; 
    text-transform: uppercase; 
}

.specs-box {
    border: 1px solid <?= $color_brand ?>;
    padding: 15px; 
    padding-bottom: 40px; 
    border-radius: 0 0 5px 5px; 
    margin-bottom: 20px;
}

.specs-desc {
    text-align: justify; 
    margin-bottom: 20px; 
    color: #444; 
    font-size: 13px;
}

/* TABLA GENERAL */
.specs-table { width: 70%; font-size: 11px; border: 1px solid <?= $color_brand ?>; border-collapse: separate; margin: 10 auto; }
.specs-table th {background-color: #fff; border-bottom: 4px solid <?= $color_brand ?>; color: <?= $color_brand ?>; text-align: left; padding: 5px;  }
.specs-table td { border-bottom: 1px solid #eee; padding: 6px; }

/* Truco CSS: Filas alternadas automáticas (adiós PHP $c % 2) */
.specs-table tbody tr:nth-child(odd) { background-color: #fff5eb; } /* Naranja muy suave */
.specs-table tbody tr:nth-child(even) { background-color: #fff; }


.specs-attr {
    font-weight: bold;
    color: #555;
    width: 40%;
    text-transform: uppercase; /* <--- ESTA LÍNEA HACE LA MAGIA */
    font-size: 10px; /* Opcional: Si al ser mayúsculas se ve muy grande, bájale un punto */
    white-space: nowrap; /* Evita que el texto se divida en varias líneas */
}

/* Columna Valor (Derecha) */
.specs-val { color: #333; width: 60%; }

/* --- SECCIÓN MOTOR --- */
.motor-header { width: 69%; margin: 0 auto; background-color: <?= $color_brand ?>; padding: 6px; color: white; font-weight: bold; font-size: 12px; margin-bottom: 0; text-transform: uppercase; }

.motor-table { width: 70%; font-size: 11px; border: 1px solid <?= $color_brand ?>; border-collapse: separate; margin: 10 auto; }
.motor-table th { background-color: #fff; border-bottom: 4px solid <?= $color_brand ?>; color: <?= $color_brand ?>; text-align: left; padding: 5px; }
.motor-table td { border-bottom: 1px solid #eee; padding: 6px; }

/* Alternado diferente para motor */
.motor-table tbody tr:nth-child(odd) { background-color: #fff5eb; }
.motor-table tbody tr:nth-child(even) { background-color: #fff; }

.motor-attr { font-weight: bold; color: #555; width: 40%; white-space: nowrap; }
.motor-val { color: #333; width: 60%; }

/* --- ESTILOS FOTOS (HOJA 3) --- */


/* Marco para cada foto individual (Vertical) */
.photo-frame {
    WIDTH: 83%;
    padding: 5px;
    border-radius: 15px;
    margin: 0px auto 15px auto;
    text-align: center;
    position: relative;
    display: inline-block; 
    
}

.photo-img {
    max-width: 100%;
    max-height: 340px; 
    object-fit: contain;
    border: 2px solid #999;
}

/* Etiqueta opcional dentro de la foto (Overlay) */
.photo-label {
    position: absolute;
    bottom: 15px; /* Ajustado para estar dentro del padding */
    right: 15px;  /* Esquina inferior derecha */
    background-color: rgba(0, 0, 0, 0.6); /* Fondo semi-transparente negro */
    color: #fff; /* Texto blanco */
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    z-index: 10;
}


h1 { font-size: 18px; border-bottom: 2px solid <?= $color_brand ?>; margin-bottom: 15px; }


/* --- ESTILOS EXCLUSIVOS PARA HOJA 2 DOBLE COLUMNA (hoja2_specs-2col.php) --- */

/* 1. Encabezado de Sección Full Width */
.section-title-2col {
    width: 100%;
    background-color: <?= $color_brand ?>;
    color: white;
    font-weight: bold;
    font-size: 15px; /* Aumentado de 14px */
    padding: 6px;
    margin-bottom: 10px;
    text-transform: uppercase;
    
}

/* 2. Contenedor de división */
.split-container-2col {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

/* 3. Tabla interna limpia */
.specs-table-2col-clean { 
    width: 100%; /* Restaurado a 100% para evitar huecos */
    font-size: 14px; /* Aumentado de 12px */
    border: 1px solid <?= $color_brand ?>; 
    border-collapse: separate; 
    border-spacing: 0;
    margin: 0; 
}

.specs-table-2col-clean th {
    
    background-color: #fff; 
    border-bottom: 4px solid <?= $color_brand ?>; 
    color: <?= $color_brand ?>; 
    text-align: center;
    padding: 5px;
    font-weight: bold;
}

.specs-table-2col-clean td { 
    border-bottom: 1px solid #eee; 
    padding: 6px; 
    vertical-align: top;
}

.specs-table-2col-clean tr:nth-child(odd) { background-color: #fff5eb; }
.specs-table-2col-clean tr:nth-child(even) { background-color: #fff; }

/* 4. Columnas internas */
.col-attr-2col {
    width: auto; /* Ancho fijo razonable para evitar colapsos */
    white-space: nowrap;  /* ELIMINAR PARA EVITAR TEXTO DIMINUTO POR AUTOSCALE */
    font-weight: bold;
    color: #555;
    text-transform: uppercase;
    padding-right: 5px;
    font-size: 11px !important; /* Forzamos tamaño legible */
}

.col-val-2col {
    width: auto;
    color: #333;
    font-size: 11px !important;
    white-space: nowrap;
}

 