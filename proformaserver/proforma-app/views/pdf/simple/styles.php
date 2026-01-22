<?php 

$color_brand = "#f37021";   // Naranja Tradimacova
$color_text  = "#333333";   // Color texto general
$color_bg_total = "#eeeeee"; // Fondo de totales
$fondo_img = __DIR__ . '/../1.png';
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
.logo-cell { vertical-align: middle; text-align: center; }

.titulo-principal {
    font-family: 'tekolocal', sans-serif;
    font-size: 31px; font-weight: bold; text-transform: uppercase;
    color: #000; margin-bottom: 5px; letter-spacing: -0.4.px; line-height: 0.9; white-space: nowrap;
}

.celda-centro { width: 60%; text-align: center; vertical-align: top; padding: 0; }
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

.caja-ruc table { width: 140%; border-collapse: collapse; text-align: center; }
.fila-ruc-numero { font-size: 27px; height: 45px; font-weight: bold; vertical-align: middle; }
.fila-label { background-color: <?= $color_brand ?>; color: white; font-size: 27px; height: 50px; font-weight: bold; vertical-align: middle; letter-spacing: 1px; }
.fila-codigo { font-size: 22px; color: #333; height: 45px; font-weight: bold; vertical-align: middle; }

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
.specs-table th {background-color: #fff; border-bottom: 4px solid <?= $color_brand ?>; color: #d35400; text-align: left; padding: 5px;  }
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
}

/* Columna Valor (Derecha) */
.specs-val { color: #333; width: 60%; }

/* --- SECCIÓN MOTOR --- */
.motor-header { width: 69%; margin: 0 auto; background-color: <?= $color_brand ?>; padding: 6px; color: white; font-weight: bold; font-size: 12px; margin-bottom: 0; text-transform: uppercase; }

.motor-table { width: 70%; font-size: 11px; border: 1px solid <?= $color_brand ?>; border-collapse: separate; margin: 10 auto; }
.motor-table th { background-color: #fff; border-bottom: 4px solid <?= $color_brand ?>; color: #d35400; text-align: left; padding: 5px; }
.motor-table td { border-bottom: 1px solid #eee; padding: 6px; }

/* Alternado diferente para motor */
.motor-table tbody tr:nth-child(odd) { background-color: #fff5eb; }
.motor-table tbody tr:nth-child(even) { background-color: #fff; }



.motor-attr { font-weight: bold; color: #555; width: 40%; }
.motor-val { color: #333; width: 60%; }

/* --- ESTILOS FOTOS (HOJA 3) --- */

/* Marco para cada foto individual (Vertical) */
.photo-frame {
    WIDTH: 70%;
    border: 1px solid <?= $color_brand ?>;
    background-color: #fafafa; 
    padding: 10px;
    border-radius: 15px;
    margin: 0px auto 15px auto;
    text-align: center;

}

.photo-img {
    max-width: 100%;
    max-height: 340px; 
    object-fit: contain;
    border: 1px solid #fff; 
}

/* Etiqueta opcional debajo de la foto (Foto 1, Foto 2) */
.photo-label {
    margin-top: 5px;
    font-size: 10px;
    color: #999;
    font-weight: bold;
    text-transform: uppercase;
}


h1 { font-size: 18px; border-bottom: 2px solid <?= $color_brand ?>; margin-bottom: 15px; }

.img-container { text-align: center; border: 1px solid #ddd; padding: 5px; }