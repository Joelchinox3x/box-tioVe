<?php
// Lógica de presentación para la tarjeta
$docLength = strlen($lead['dni_ruc']);
$isRuc = $docLength === 11;
$nameLabel = $isRuc ? 'CLIENTE' : 'EMPRESA'; // Nota: En el original era al revés para 8 digitos (DNI=Cliente). 
// Original: $isRuc = $docLength === 8; -> Si length 8 es TRUE, entonces es DNI. 
// PERO la variable se llama $isRuc. Si docLength es 8, $isRuc es TRUE? No, eso sería confuso.
// Revisando código original:
// $docLength = strlen($lead['dni_ruc']);
// $isRuc = $docLength === 8;  <-- Esto parece un error de nombrado en el original o una lógica inversa.
// Si es 8 digitos, es DNI. Si es 11, es RUC.
// En original: $isRuc = ($len === 8). Si es 8, $isRuc es true.
// $nameLabel = $isRuc ? 'CLIENTE' : 'EMPRESA'; -> Si es 8 (true), label es CLIENTE. Correcto para DNI.
// $docLabel = $isRuc ? 'DNI' : 'RUC'; -> Si es 8 (true), label es DNI. Correcto.

// CORRECCIÓN DE NOMBRADO PARA CLARIDAD, MANTENIENDO LÓGICA VISUAL:
$isDni = (strlen($lead['dni_ruc']) === 8);
$isRucReal = !$isDni; // Asumimos RUC si no es DNI (11 digitos)

// Mapeo de variables de presentación basado en el código original pero con nombres lógicos
$nameLabel = $isDni ? 'CLIENTE' : 'EMPRESA';
$docLabel = $isDni ? 'DNI' : 'RUC';

// Colores premium (Original usa $isRuc que era "es de 8 digitos")
// Si era 8 dígitos (violeta/morado en lógica original para DNI? O RUC?)
// Original: 
// $isRuc = $docLength === 8;
// $accentColor = $isRuc ? 'violet' : 'blue'; 
// Osea, si es 8 dígitos (DNI), usaba 'violet'. Si es 11 (RUC), usaba 'blue'.

$accentColor = $isDni ? 'violet' : 'blue';
$labelColor = $isDni ? 'text-violet-600' : 'text-blue-600';
$docBg = $isDni ? 'bg-violet-50' : 'bg-blue-50';
$docColor = $isDni ? 'text-violet-700' : 'text-blue-700';
$borderColor = $isDni ? 'border-violet-200' : 'border-blue-200';
$hoverBorder = $isDni ? 'hover:border-violet-400' : 'hover:border-blue-400';
$indicatorBg = $isDni ? 'bg-violet-500' : 'bg-blue-500';
?>

<!-- TARJETA MEJORADA -->
<div class="group relative <?= $docBg ?> rounded-2xl shadow-md border-2 border-slate-100 <?= $hoverBorder ?> hover:shadow-2xl hover:bg-white transition-all duration-300 overflow-hidden">

    <!-- Indicador lateral vibrante -->
    <div class="absolute left-0 top-0 bottom-0 w-2 <?= $indicatorBg ?> opacity-70 group-hover:opacity-100 group-hover:w-2.5 transition-all"></div>

    <div class="flex items-stretch">

        <!-- CONTENIDO PRINCIPAL -->
        <div class="flex-1 p-3 pl-4 flex gap-2.5 items-center min-w-0">

            <!-- Avatar Premium -->
            <div class="flex-shrink-0">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 flex items-center justify-center font-black text-2xl border-2 border-white shadow-lg group-hover:from-<?= $accentColor ?>-100 group-hover:to-<?= $accentColor ?>-200 group-hover:text-<?= $accentColor ?>-700 group-hover:scale-105 transition-all duration-300">
                    <?= strtoupper(substr($lead['nombre'], 0, 1)) ?>
                </div>
            </div>

            <!-- Información -->
            <div class="flex-1 min-w-0">

                <!-- Label + Fecha -->
                <div class="flex justify-between items-start mb-1">
                    <span class="text-[11px] font-extrabold <?= $labelColor ?> uppercase tracking-widest flex items-center gap-1.5">
                        <i class="ph-fill ph-<?= $isDni ? 'user-circle' : 'buildings' ?> text-sm"></i>
                        <?= $nameLabel ?>
                    </span>

                    <!-- Fecha más pequeña y pegada a la derecha -->
                    <div class="bg-slate-50 text-slate-400 text-[9px] font-semibold px-1.5 py-0.5 rounded border border-slate-200 flex items-center gap-1 -mr-1">
                        <span><?= date('d/m', strtotime($lead['fecha_creacion'])) ?></span>
                        <span class="text-slate-300">•</span>
                        <span><?= date('H:i', strtotime($lead['fecha_creacion'])) ?></span>
                    </div>
                </div>

                <!-- NOMBRE GRANDE Y DESTACADO -->
                <h3 class="font-black text-slate-900 text-sm leading-tight mb-2.5 group-hover:text-<?= $accentColor ?>-700 transition-colors line-clamp-2" title="<?= htmlspecialchars($lead['nombre']) ?>">
                    <?= htmlspecialchars($lead['nombre']) ?>
                </h3>

                <!-- Documento + Teléfono -->
                <div class="flex flex-col gap-2">
                    <!-- Fila DNI/RUC con Botón Buscar (posición fija) -->
                    <div class="flex items-center justify-between gap-2">
                        <!-- Badge de Documento Premium -->
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg <?= $docBg ?> <?= $docColor ?> text-xs font-bold border <?= $borderColor ?> shadow-sm">
                            <span class="opacity-70"><?= $docLabel ?></span>
                            <span><?= htmlspecialchars($lead['dni_ruc']) ?></span>
                        </span>

                        <!-- Botón Buscar DNI/RUC (posición fija a la derecha) -->
                        <button
                            type="button"
                            onclick="abrirModalConfirmacion(<?= $lead['id'] ?>, <?= htmlspecialchars(json_encode($lead['dni_ruc']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($lead['nombre']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($lead['direccion'] ?? ''), ENT_QUOTES) ?>)"
                            class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-300 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1.5"
                            title="Consultar RENIEC/SUNAT"
                        >
                            <i class="ph-bold ph-magnifying-glass text-xs"></i>
                        </button>
                    </div>

                    <!-- Teléfono -->
                    <?php if(!empty($lead['telefono'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead['telefono']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-50 text-green-700 text-xs font-bold border border-green-200 hover:bg-green-100 transition-all shadow-sm w-fit">
                        <i class="ph-bold ph-whatsapp-logo text-sm"></i>
                        <?= htmlspecialchars($lead['telefono']) ?>
                    </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- ACCIONES -->
        <div class="w-[72px] bg-gradient-to-b from-slate-50 to-slate-100 border-l-2 border-slate-200 flex flex-col divide-y-2 divide-slate-200 flex-shrink-0">

            <!-- Aprobar -->
            <button onclick="handleApprove(<?= $lead['id'] ?>, this)" class="flex-1 w-full h-full flex flex-col items-center justify-center gap-0.5 bg-gradient-to-br from-emerald-50 to-green-50 hover:from-emerald-100 hover:to-green-100 transition-all group/btn" title="Aprobar">
                <i class="ph-fill ph-check-circle text-[28px] text-emerald-500 group-hover/btn:scale-125 group-hover/btn:rotate-12 transition-transform duration-300"></i>
                <span class="text-[9px] font-extrabold text-emerald-700 uppercase tracking-tight">Aprobar</span>
            </button>

            <!-- Rechazar -->
            <button onclick="handleReject(<?= $lead['id'] ?>, this)" class="h-11 w-full flex items-center justify-center bg-gradient-to-r from-red-50 to-pink-50 hover:from-red-100 hover:to-pink-100 transition-all group/btn" title="Rechazar">
                <i class="ph-bold ph-x text-sm text-red-500 group-hover/btn:scale-125 transition-transform"></i>
                <span class="text-[9px] font-bold text-red-600 ml-0.5">Rechazar</span>
            </button>

        </div>

    </div>
</div>
